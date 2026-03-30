<?php

declare(strict_types=1);

namespace Typst\Tests;

use PHPUnit\Framework\TestCase;
use Typst\Compiler;
use Typst\Document;
use Typst\Exception\LogicException;
use Typst\Exception\RuntimeException;
use Typst\PendingDocument;
use Typst\World;

final class PendingDocumentTest extends TestCase
{
    private World $world;
    private Compiler $compiler;

    protected function setUp(): void
    {
        $this->world = new World();
        $this->compiler = new Compiler($this->world);
    }

    public function testCompileInBackgroundReturnsPendingDocument(): void
    {
        $source = $this->world->loadString('#set page(height: auto)' . "\nHello");
        $pending = $this->compiler->compileInBackground($source);

        static::assertInstanceOf(PendingDocument::class, $pending);
    }

    public function testJoinReturnsDocument(): void
    {
        $source = $this->world->loadString('#set page(height: auto)' . "\nHello");
        $pending = $this->compiler->compileInBackground($source);
        $document = $pending->join();

        static::assertInstanceOf(Document::class, $document);
        static::assertSame(1, $document->pageCount());
    }

    public function testJoinProducesSameResultAsCompile(): void
    {
        $source = $this->world->loadString('#set page(height: auto)' . "\nHello, World!");
        $syncDoc = $this->compiler->compile($source);
        $asyncDoc = $this->compiler->compileInBackground($source)->join();

        $syncPdf = (string) $syncDoc->toPdf();
        $asyncPdf = (string) $asyncDoc->toPdf();

        static::assertSame(strlen($syncPdf), strlen($asyncPdf));
        static::assertSame($syncDoc->pageCount(), $asyncDoc->pageCount());
    }

    public function testJoinTwiceThrowsLogicException(): void
    {
        $source = $this->world->loadString('#set page(height: auto)' . "\nHello");
        $pending = $this->compiler->compileInBackground($source);
        $pending->join();

        $this->expectException(LogicException::class);
        $pending->join();
    }

    public function testGetNotificationStreamReturnsResource(): void
    {
        $source = $this->world->loadString('#set page(height: auto)' . "\nHello");
        $pending = $this->compiler->compileInBackground($source);
        $stream = $pending->getNotificationStream();

        static::assertIsResource($stream); // @mago-expect analysis:redundant-type-comparison - runtime check
    }

    public function testGetNotificationStreamAfterJoinThrowsLogicException(): void
    {
        $source = $this->world->loadString('#set page(height: auto)' . "\nHello");
        $pending = $this->compiler->compileInBackground($source);
        $pending->join();

        $this->expectException(LogicException::class);
        $pending->getNotificationStream();
    }

    public function testNotificationStreamBecomesReadable(): void
    {
        $source = $this->world->loadString('#set page(height: auto)' . "\nHello");
        $pending = $this->compiler->compileInBackground($source);
        $stream = $pending->getNotificationStream();

        $read = [$stream];
        $write = [];
        $except = [];
        $ready = stream_select($read, $write, $except, 10, 0);

        static::assertGreaterThan(0, $ready, 'Notification stream should become readable');
        static::assertTrue($pending->isReady());
    }

    public function testIsReadyReturnsFalseThenTrue(): void
    {
        $source = $this->world->loadString('#set page(height: auto)' . "\nHello");
        $pending = $this->compiler->compileInBackground($source);

        $stream = $pending->getNotificationStream();
        $read = [$stream];
        $write = [];
        $except = [];
        stream_select($read, $write, $except, 10, 0);

        static::assertTrue($pending->isReady());
    }

    public function testCompileInBackgroundWithInputs(): void
    {
        $source = $this->world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("name")');
        $document = $this->compiler->compileInBackground($source, ['name' => 'Claude'])->join();

        static::assertInstanceOf(Document::class, $document);
    }

    public function testCompileInBackgroundInvalidSourceThrows(): void
    {
        $source = $this->world->loadString('#invalid-function()');
        $pending = $this->compiler->compileInBackground($source);

        $this->expectException(RuntimeException::class);
        $pending->join();
    }

    public function testMultipleConcurrentCompilations(): void
    {
        $sources = [];
        $pendings = [];

        for ($i = 0; $i < 5; $i++) {
            $sources[] = $this->world->loadString("#set page(height: auto)\nDocument {$i}");
            $pendings[] = $this->compiler->compileInBackground($sources[$i]);
        }

        $documents = [];
        foreach ($pendings as $pending) {
            $documents[] = $pending->join();
        }

        static::assertCount(5, $documents);
        foreach ($documents as $doc) {
            static::assertInstanceOf(Document::class, $doc);
            static::assertSame(1, $doc->pageCount());
        }
    }

    public function testMultipleConcurrentCompilationsWithStreamSelect(): void
    {
        $pendings = [];
        $streams = [];

        for ($i = 0; $i < 5; $i++) {
            $source = $this->world->loadString("#set page(height: auto)\nDocument " . $i);
            $pending = $this->compiler->compileInBackground($source);
            $pendings[] = $pending;
            $streams[] = $pending->getNotificationStream();
        }

        $remaining = $streams;
        while (count($remaining) > 0) {
            $read = $remaining;
            $write = [];
            $except = [];
            stream_select($read, $write, $except, 10, 0);
            $remaining = array_diff_key($remaining, array_flip(array_keys($read)));
        }

        foreach ($pendings as $pending) {
            static::assertTrue($pending->isReady());
            $doc = $pending->join();
            static::assertInstanceOf(Document::class, $doc);
        }
    }

    public function testCompileInBackgroundMultiPageDocument(): void
    {
        $source = $this->world->loadString("Page 1\n#pagebreak()\nPage 2\n#pagebreak()\nPage 3");
        $document = $this->compiler->compileInBackground($source)->join();

        static::assertSame(3, $document->pageCount());
    }

    public function testCompileInBackgroundEmptySource(): void
    {
        $source = $this->world->loadString('');
        $document = $this->compiler->compileInBackground($source)->join();

        static::assertInstanceOf(Document::class, $document);
    }

    public function testCompileInBackgroundSourceFromFile(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'typst_test_');
        static::assertNotFalse($tmp);
        file_put_contents($tmp, "#set page(height: auto)\nHello from file");

        try {
            $source = $this->world->loadFile($tmp);
            $document = $this->compiler->compileInBackground($source)->join();

            static::assertInstanceOf(Document::class, $document);
        } finally {
            unlink($tmp);
        }
    }

    public function testCompileInBackgroundSourceFromDifferentWorldThrows(): void
    {
        $otherWorld = new World();
        $source = $otherWorld->loadString('Hello');

        $this->expectException(\Typst\Exception\InvalidArgumentException::class);
        $this->compiler->compileInBackground($source);
    }

    public function testDropWithoutJoinDoesNotLeak(): void
    {
        $source = $this->world->loadString('#set page(height: auto)' . "\nHello");

        for ($i = 0; $i < 10; $i++) {
            $pending = $this->compiler->compileInBackground($source);
            unset($pending);
        }

        static::assertTrue(true);
    }

    public function testGetNotificationStreamCanBeCalledMultipleTimes(): void
    {
        $source = $this->world->loadString('#set page(height: auto)' . "\nHello");
        $pending = $this->compiler->compileInBackground($source);

        $stream1 = $pending->getNotificationStream();
        $stream2 = $pending->getNotificationStream();

        static::assertIsResource($stream1); // @mago-expect analysis:redundant-type-comparison - runtime check.
        static::assertIsResource($stream2); // @mago-expect analysis:redundant-type-comparison - runtime check.

        $pending->join();
    }

    public function testExportAfterBackgroundCompilation(): void
    {
        $source = $this->world->loadString('#set page(height: auto)' . "\n= Hello\nWorld");
        $document = $this->compiler->compileInBackground($source)->join();

        $pdf = $document->toPdf();
        static::assertGreaterThan(0, $pdf->size());

        $image = $document->toImage();
        static::assertGreaterThan(0, $image->size());
        static::assertGreaterThan(0, $image->width());
        static::assertGreaterThan(0, $image->height());

        $svg = $document->toSvg();
        static::assertGreaterThan(0, $svg->size());
    }
}
