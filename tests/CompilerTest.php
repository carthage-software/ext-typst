<?php

declare(strict_types=1);

namespace Typst\Tests;

use PHPUnit\Framework\TestCase;
use Typst\Compiler;
use Typst\Document;
use Typst\Exception\RuntimeException;
use Typst\World;

final class CompilerTest extends TestCase
{
    public function testConstruction(): void
    {
        $world = new World();
        $c = new Compiler($world);
        static::assertInstanceOf(Compiler::class, $c);
    }

    public function testConstructionWithoutDefaultFonts(): void
    {
        $world = new World(embed_default_fonts: false);
        $c = new Compiler($world);
        static::assertInstanceOf(Compiler::class, $c);
    }

    public function testCompileSimpleSource(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello, World!");
        $doc = $c->compile($source);
        static::assertInstanceOf(Document::class, $doc);
    }

    public function testCompileInvalidSourceThrows(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString('#invalid-function()');
        $this->expectException(RuntimeException::class);
        $c->compile($source);
    }

    public function testCompileWithInputs(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("name")');
        $doc = $c->compile($source, ['name' => 'Claude']);
        static::assertInstanceOf(Document::class, $doc);
    }

    public function testCompileEmptySource(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString('');
        $doc = $c->compile($source);
        static::assertInstanceOf(Document::class, $doc);
    }

    public function testCompileMultiPageDocument(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString("Page 1\n#pagebreak()\nPage 2\n#pagebreak()\nPage 3");
        $doc = $c->compile($source);
        static::assertSame(3, $doc->pageCount());
    }

    public function testCompileFileValid(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'typst_test_');
        static::assertNotFalse($tmp);
        file_put_contents($tmp, "#set page(height: auto)\nHello from file");
        try {
            $world = new World();
            $c = new Compiler($world);
            $source = $world->loadFile($tmp);
            $doc = $c->compile($source);
            static::assertInstanceOf(Document::class, $doc);
        } finally {
            unlink($tmp);
        }
    }

    public function testCompileFileNonExistentThrows(): void
    {
        $world = new World();
        $this->expectException(RuntimeException::class);
        $world->loadFile('/nonexistent/path/file.typ');
    }

    public function testCompileFileWithInputs(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'typst_test_');
        static::assertNotFalse($tmp);
        file_put_contents($tmp, '#set page(height: auto)' . "\n" . '#sys.inputs.at("key")');
        try {
            $world = new World();
            $c = new Compiler($world);
            $source = $world->loadFile($tmp);
            $doc = $c->compile($source, ['key' => 'value']);
            static::assertInstanceOf(Document::class, $doc);
        } finally {
            unlink($tmp);
        }
    }

    public function testCompileFileInvalidTypstThrows(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'typst_test_');
        static::assertNotFalse($tmp);
        file_put_contents($tmp, '#unknown-func()');
        try {
            $world = new World();
            $c = new Compiler($world);
            $source = $world->loadFile($tmp);
            $this->expectException(RuntimeException::class);
            $c->compile($source);
        } finally {
            @unlink($tmp);
        }
    }

    public function testCompileFileWithTemplateDirResolvesImports(): void
    {
        $dir = sys_get_temp_dir() . '/typst_test_' . uniqid();
        mkdir($dir, 0o777, true);
        file_put_contents($dir . '/lib.typ', '#let greet(name) = [Hello, #name!]');
        file_put_contents($dir . '/main.typ', "#import \"lib.typ\": greet\n#set page(height: auto)\n#greet(\"World\")");
        try {
            $world = new World(template_dir: $dir);
            $c = new Compiler($world);
            $source = $world->loadFile($dir . '/main.typ');
            $doc = $c->compile($source);
            static::assertInstanceOf(Document::class, $doc);
        } finally {
            unlink($dir . '/lib.typ');
            unlink($dir . '/main.typ');
            rmdir($dir);
        }
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

    public function testCloneProducesIndependentInstance(): void
    {
        $world = new World();
        $c1 = new Compiler($world);
        $c2 = clone $c1;

        $s1 = $world->loadString("#set page(height: auto)\nHello");
        $s2 = $world->loadString("#set page(height: auto)\nWorld");
        $doc1 = $c1->compile($s1);
        $doc2 = $c2->compile($s2);
        static::assertSame(1, $doc1->pageCount());
        static::assertSame(1, $doc2->pageCount());
    }

    public function testCompileFileWithInputsVerifiesValue(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'typst_test_');
        static::assertNotFalse($tmp);
        file_put_contents($tmp, '#set page(height: auto)' . "\n" . '#sys.inputs.at("greeting")');
        try {
            $world = new World();
            $c = new Compiler($world);
            $source = $world->loadFile($tmp);
            $doc = $c->compile($source, ['greeting' => 'Hello World']);
            static::assertInstanceOf(Document::class, $doc);
            static::assertSame(1, $doc->pageCount());
        } finally {
            unlink($tmp);
        }
    }

    public function testSourceReusableAcrossCompilations(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $doc1 = $c->compile($source);
        $doc2 = $c->compile($source);
        static::assertSame($doc1->pageCount(), $doc2->pageCount());
    }

    public function testCompileStringConvenience(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $doc = $c->compileString("#set page(height: auto)\nHello, World!");
        static::assertInstanceOf(Document::class, $doc);
    }

    public function testCompileFileConvenience(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'typst_test_');
        static::assertNotFalse($tmp);
        file_put_contents($tmp, "#set page(height: auto)\nHello from file");
        try {
            $world = new World();
            $c = new Compiler($world);
            $doc = $c->compileFile($tmp);
            static::assertInstanceOf(Document::class, $doc);
        } finally {
            unlink($tmp);
        }
    }

    public function testGetWorld(): void
    {
        $world = new World();
        $c = new Compiler($world);
        static::assertInstanceOf(World::class, $c->getWorld());
    }

    public function testClonedCompilerResolvesTemplateDir(): void
    {
        $world = new World(template_dir: __DIR__ . '/fixtures');
        $compiler = new Compiler($world);
        $clone = clone $compiler;

        $document = $clone->compileFile('hello.typ');
        static::assertSame(1, $document->pageCount());
    }
}
