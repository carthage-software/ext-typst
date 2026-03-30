<?php

declare(strict_types=1);

namespace Typst\Tests;

use PHPUnit\Framework\TestCase;
use Typst\Compiler;
use Typst\Document;
use Typst\Exception\RuntimeException;
use Typst\Inspector;
use Typst\World;

final class StabilityTest extends TestCase
{
    public function testRepeatedCompilations(): void
    {
        $world = new World();
        $c = new Compiler($world);
        for ($i = 0; $i < 50; $i++) {
            $source = $world->loadString("#set page(height: auto)\nIteration {$i}");
            $doc = $c->compile($source);
            static::assertSame(1, $doc->pageCount());
        }
    }

    public function testRepeatedInspections(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        for ($i = 0; $i < 50; $i++) {
            $source = $world->loadString("#set page(height: auto)\nIteration {$i}");
            $result = $inspector->inspect($source);
            static::assertTrue($result->success());
        }
    }

    public function testRepeatedPdfExport(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $doc = $c->compile($source);
        for ($i = 0; $i < 20; $i++) {
            $pdf = $doc->toPdf();
            static::assertGreaterThan(0, $pdf->size());
        }
    }

    public function testRepeatedImageExport(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $doc = $c->compile($source);
        for ($i = 0; $i < 20; $i++) {
            $img = $doc->toImage();
            static::assertGreaterThan(0, $img->size());
        }
    }

    public function testRepeatedSvgExport(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $doc = $c->compile($source);
        for ($i = 0; $i < 20; $i++) {
            $svg = $doc->toSvg();
            static::assertGreaterThan(0, $svg->size());
        }
    }

    public function testRepeatedFailedCompilations(): void
    {
        $world = new World();
        $c = new Compiler($world);
        for ($i = 0; $i < 20; $i++) {
            try {
                $source = $world->loadString('#unknown-func()');
                $c->compile($source);
                static::fail('Expected RuntimeException');
            } catch (RuntimeException) {
                // @mago-expect lint:no-empty-catch-clause
            }
        }

        $source = $world->loadString("#set page(height: auto)\nStill works");
        $doc = $c->compile($source);
        static::assertSame(1, $doc->pageCount());
    }

    public function testUnicodeContent(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHéllo Wörld! 日本語 中文 한국어");
        $doc = $c->compile($source);
        static::assertSame(1, $doc->pageCount());
    }

    public function testUnicodeEmoji(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\n🎉🚀✨");
        $doc = $c->compile($source);
        static::assertSame(1, $doc->pageCount());
    }

    public function testUnicodeInInputs(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("name")');
        $doc = $c->compile($source, ['name' => '日本語テスト']);
        static::assertSame(1, $doc->pageCount());
    }

    public function testEmptySourceCompiles(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString('');
        $doc = $c->compile($source);
        static::assertInstanceOf(Document::class, $doc);
        static::assertSame(1, $doc->pageCount());
    }

    public function testEmptyInputsSameAsNull(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $doc1 = $c->compile($source, []);
        $doc2 = $c->compile($source, null);
        static::assertSame($doc1->pageCount(), $doc2->pageCount());
    }

    public function testLargeDocument(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $lines = ['#set page(height: auto)'];
        for ($i = 0; $i < 100; $i++) {
            $lines[] = "Line {$i}: Lorem ipsum dolor sit amet.";
        }
        $source = $world->loadString(implode("\n", $lines));
        $doc = $c->compile($source);
        static::assertGreaterThanOrEqual(1, $doc->pageCount());
    }

    public function testManyPagesDocument(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $pages = [];
        for ($i = 1; $i <= 10; $i++) {
            $pages[] = "Page {$i}";
        }
        $source = $world->loadString(implode("\n#pagebreak()\n", $pages));
        $doc = $c->compile($source);
        static::assertSame(10, $doc->pageCount());
    }

    public function testToImagesOnManyPages(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $pages = [];
        for ($i = 1; $i <= 5; $i++) {
            $pages[] = "Page {$i}";
        }
        $source = $world->loadString(implode("\n#pagebreak()\n", $pages));
        $doc = $c->compile($source);
        $images = $doc->toImages();
        static::assertCount(5, $images);
    }

    public function testClonedCompilerIndependent(): void
    {
        $world = new World();
        $c1 = new Compiler($world);
        $c2 = clone $c1;

        $s1 = $world->loadString("#set page(height: auto)\nFrom c1");
        $s2 = $world->loadString("#set page(height: auto)\nFrom c2");
        $doc1 = $c1->compile($s1);
        $doc2 = $c2->compile($s2);

        static::assertSame(1, $doc1->pageCount());
        static::assertSame(1, $doc2->pageCount());
    }

    public function testClonedWorldIndependent(): void
    {
        $fontA = __DIR__ . '/fixtures/fonts/Roboto/static/Roboto-Regular.ttf';
        $fontB = __DIR__ . '/fixtures/fonts/Roboto/static/Roboto-Bold.ttf';

        $world = new World();
        $worldClone = clone $world;

        $world->addFontFile($fontA);
        $worldClone->addFontFile($fontB);

        $c1 = new Compiler($world);
        $c2 = new Compiler($worldClone);

        $source = $world->loadString("#set page(height: auto)\nHello");
        $doc1 = $c1->compile($source);
        $source2 = $worldClone->loadString("#set page(height: auto)\nHello");
        $doc2 = $c2->compile($source2);
        static::assertSame(1, $doc1->pageCount());
        static::assertSame(1, $doc2->pageCount());
    }

    public function testMultipleCompilerInstances(): void
    {
        $world = new World();
        $compilers = [];
        for ($i = 0; $i < 5; $i++) {
            $compilers[] = new Compiler($world);
        }

        foreach ($compilers as $i => $c) {
            $source = $world->loadString("#set page(height: auto)\nCompiler {$i}");
            $doc = $c->compile($source);
            static::assertSame(1, $doc->pageCount());
        }
    }

    public function testPdfOutlivesDocument(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $pdf = $c->compile($source)->toPdf();
        static::assertGreaterThan(0, $pdf->size());
        static::assertStringStartsWith('%PDF', $pdf->bytes());
    }

    public function testImageOutlivesDocument(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $img = $c->compile($source)->toImage();
        static::assertGreaterThan(0, $img->size());
        static::assertGreaterThan(0, $img->width());
    }

    public function testSvgOutlivesDocument(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $svg = $c->compile($source)->toSvg();
        static::assertGreaterThan(0, $svg->size());
        static::assertStringContainsString('<svg', $svg->bytes());
    }

    public function testVersionIsSemver(): void
    {
        static::assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', \Typst\version());
    }

    public function testTypstVersionIsSemver(): void
    {
        static::assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', \Typst\typst_version());
    }

    public function testNoCachingWithZeroCacheSize(): void
    {
        $world = new World(cache_size: 0);
        $c = new Compiler($world);
        for ($i = 0; $i < 10; $i++) {
            $source = $world->loadString("#set page(height: auto)\nIteration {$i}");
            $doc = $c->compile($source);
            static::assertSame(1, $doc->pageCount());
        }
    }
}
