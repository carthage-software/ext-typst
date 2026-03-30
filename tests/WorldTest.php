<?php

declare(strict_types=1);

namespace Typst\Tests;

use PHPUnit\Framework\TestCase;
use Typst\Compiler;
use Typst\Exception\InvalidArgumentException;
use Typst\Exception\RuntimeException;
use Typst\Inspector;
use Typst\Source;
use Typst\World;

/**
 * @mago-expect analysis:redundant-type-comparison - runtime checks, verifying API
 */
final class WorldTest extends TestCase
{
    public function testDefaultConstruction(): void
    {
        $world = new World();
        static::assertInstanceOf(World::class, $world);
    }

    public function testConstructionWithTemplateDir(): void
    {
        $world = new World(template_dir: __DIR__ . '/fixtures');
        static::assertInstanceOf(World::class, $world);
    }

    public function testConstructionWithCacheSize(): void
    {
        $world = new World(cache_size: 128);
        static::assertInstanceOf(World::class, $world);
    }

    public function testConstructionWithAllParameters(): void
    {
        $world = new World(template_dir: __DIR__ . '/fixtures', package_dir: '/tmp/packages', cache_size: 128);
        static::assertInstanceOf(World::class, $world);
    }

    public function testConstructionWithCacheSizeZero(): void
    {
        $world = new World(cache_size: 0);
        static::assertInstanceOf(World::class, $world);
    }

    public function testConstructionWithoutDefaultFonts(): void
    {
        $world = new World(embed_default_fonts: false);
        static::assertInstanceOf(World::class, $world);
    }

    public function testConstructionWithFontDirs(): void
    {
        $world = new World(font_dirs: [__DIR__ . '/fixtures/fonts']);
        static::assertInstanceOf(World::class, $world);
    }

    public function testConstructionWithAllFontParameters(): void
    {
        $world = new World(embed_default_fonts: false, font_dirs: [__DIR__ . '/fixtures/fonts']);
        static::assertInstanceOf(World::class, $world);
    }

    public function testLoadStringReturnsSource(): void
    {
        $world = new World();
        $source = $world->loadString("#set page(height: auto)\nHello");
        static::assertInstanceOf(Source::class, $source);
    }

    public function testLoadStringEmptySource(): void
    {
        $world = new World();
        $source = $world->loadString('');
        static::assertInstanceOf(Source::class, $source);
    }

    public function testLoadStringTextPreserved(): void
    {
        $world = new World();
        $text = "#set page(height: auto)\nHello, World!";
        $source = $world->loadString($text);
        static::assertSame($text, $source->getText());
    }

    public function testLoadStringReturnsUniqueIds(): void
    {
        $world = new World();
        $s1 = $world->loadString('Hello');
        $s2 = $world->loadString('World');
        static::assertNotSame($s1->getId(), $s2->getId());
    }

    public function testLoadFileReturnsSource(): void
    {
        $world = new World();
        $source = $world->loadFile(__DIR__ . '/fixtures/hello.typ');
        static::assertInstanceOf(Source::class, $source);
    }

    public function testLoadFileTextMatchesFileContent(): void
    {
        $world = new World();
        $source = $world->loadFile(__DIR__ . '/fixtures/hello.typ');
        $expected = file_get_contents(__DIR__ . '/fixtures/hello.typ');
        static::assertSame($expected, $source->getText());
    }

    public function testLoadFileNonExistentThrows(): void
    {
        $world = new World();
        $this->expectException(RuntimeException::class);
        $world->loadFile('/nonexistent/path/file.typ');
    }

    public function testLoadFileTempFile(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'typst_test_');
        static::assertNotFalse($tmp);
        file_put_contents($tmp, "#set page(height: auto)\nTemp file");
        try {
            $world = new World();
            $source = $world->loadFile($tmp);
            static::assertSame("#set page(height: auto)\nTemp file", $source->getText());
        } finally {
            unlink($tmp);
        }
    }

    public function testSourceIdIsInt(): void
    {
        $world = new World();
        $source = $world->loadString('Hello');
        static::assertIsInt($source->getId());
    }

    public function testSourceTextIsString(): void
    {
        $world = new World();
        $source = $world->loadString('Hello');
        static::assertIsString($source->getText());
    }

    public function testSourceReusableAcrossMultipleCompilations(): void
    {
        $world = new World();
        $source = $world->loadString("#set page(height: auto)\nHello");
        $text1 = $source->getText();
        $text2 = $source->getText();
        static::assertSame($text1, $text2);
    }

    public function testAddFontFileValid(): void
    {
        $world = new World();
        $world->addFontFile(__DIR__ . '/fixtures/fonts/Roboto/static/Roboto-Regular.ttf');

        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello with custom font");
        $doc = $c->compile($source);
        static::assertSame(1, $doc->pageCount());
    }

    public function testAddFontFileNonExistentThrows(): void
    {
        $world = new World();
        $this->expectException(RuntimeException::class);
        $world->addFontFile('/nonexistent/font.ttf');
    }

    public function testAddFontDataValid(): void
    {
        $data = file_get_contents(__DIR__ . '/fixtures/fonts/Roboto/static/Roboto-Regular.ttf');
        static::assertNotFalse($data);

        $world = new World();
        $world->addFontData($data);

        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello with binary font data");
        $doc = $c->compile($source);
        static::assertSame(1, $doc->pageCount());
    }

    public function testAddFontDataInvalidThrows(): void
    {
        $world = new World();
        $this->expectException(RuntimeException::class);
        $world->addFontData('not a font');
    }

    public function testAddFontDataEmptyStringThrows(): void
    {
        $world = new World();
        $this->expectException(RuntimeException::class);
        $world->addFontData('');
    }

    public function testCloneCreatesIndependentCopy(): void
    {
        $world = new World();
        $clone = clone $world;

        $clone->addFontFile(__DIR__ . '/fixtures/fonts/Roboto/static/Roboto-Regular.ttf');

        $c1 = new Compiler($world);
        $c2 = new Compiler($clone);

        $source = $world->loadString("#set page(height: auto)\nHello");
        $doc1 = $c1->compile($source);
        $source2 = $clone->loadString("#set page(height: auto)\nHello");
        $doc2 = $c2->compile($source2);
        static::assertSame(1, $doc1->pageCount());
        static::assertSame(1, $doc2->pageCount());
    }

    public function testSharedWorldAcrossCompilers(): void
    {
        $world = new World();
        $c1 = new Compiler($world);
        $c2 = new Compiler($world);

        $world->addFontFile(__DIR__ . '/fixtures/fonts/Roboto/static/Roboto-Regular.ttf');

        $source = $world->loadString("#set page(height: auto)\nHello");
        $doc1 = $c1->compile($source);
        $doc2 = $c2->compile($source);
        static::assertSame(1, $doc1->pageCount());
        static::assertSame(1, $doc2->pageCount());
    }

    public function testMultipleFontsAdded(): void
    {
        $world = new World();
        $world->addFontFile(__DIR__ . '/fixtures/fonts/Roboto/static/Roboto-Regular.ttf');
        $world->addFontFile(__DIR__ . '/fixtures/fonts/Roboto/static/Roboto-Bold.ttf');

        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $doc = $c->compile($source);
        static::assertSame(1, $doc->pageCount());
    }

    public function testLoadFileRelativeToTemplateDir(): void
    {
        $world = new World(template_dir: __DIR__ . '/fixtures');
        $source = $world->loadFile('hello.typ');
        $expected = file_get_contents(__DIR__ . '/fixtures/hello.typ');
        static::assertSame($expected, $source->getText());
    }

    public function testCompileFileRelativeToTemplateDir(): void
    {
        $world = new World(template_dir: __DIR__ . '/fixtures');
        $compiler = new Compiler($world);
        $doc = $compiler->compileFile('hello.typ');
        static::assertSame(1, $doc->pageCount());
    }

    public function testCrossWorldSourceThrows(): void
    {
        $world1 = new World();
        $world2 = new World();

        $source = $world1->loadString("#set page(height: auto)\nHello");
        $compiler = new Compiler($world2);

        $this->expectException(\Typst\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('different World');
        $compiler->compile($source);
    }

    public function testClonedWorldSourcesAreCompatible(): void
    {
        $world = new World();
        $clone = clone $world;

        $source = $world->loadString("#set page(height: auto)\nHello");
        $compiler = new Compiler($clone);
        $doc = $compiler->compile($source);
        static::assertSame(1, $doc->pageCount());
    }

    public function testGetFontFamiliesReturnsArray(): void
    {
        $world = new World();
        $families = $world->getFontFamilies();
        static::assertIsArray($families);
        static::assertNotEmpty($families);
    }

    public function testGetFontFamiliesContainsDefaultFonts(): void
    {
        $world = new World();
        $families = $world->getFontFamilies();
        static::assertContains('New Computer Modern', $families);
    }

    public function testGetFontFamiliesEmptyWithoutDefaults(): void
    {
        $world = new World(embed_default_fonts: false);
        $families = $world->getFontFamilies();
        static::assertEmpty($families);
    }

    public function testGetFontFamiliesIncludesAddedFont(): void
    {
        $world = new World(embed_default_fonts: false);
        $world->addFontFile(__DIR__ . '/fixtures/fonts/Roboto/static/Roboto-Regular.ttf');
        $families = $world->getFontFamilies();
        static::assertContains('Roboto', $families);
    }

    public function testFontDirDoesNotExistThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist');
        new World(font_dirs: ['/nonexistent/font/directory']);
    }

    public function testFontDirIsFileThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not a directory');
        new World(font_dirs: [__FILE__]);
    }

    public function testCompilerClearCacheReturnsInt(): void
    {
        $world = new World(template_dir: __DIR__ . '/fixtures');
        $compiler = new Compiler($world);

        $compiler->compileFile('hello.typ');

        $cleared = $compiler->clearCache();
        static::assertIsInt($cleared);
        static::assertGreaterThanOrEqual(0, $cleared);
    }

    public function testCompilerClearCacheEmptyReturnsZero(): void
    {
        $world = new World();
        $compiler = new Compiler($world);

        $cleared = $compiler->clearCache();
        static::assertSame(0, $cleared);
    }

    public function testInspectorClearCacheReturnsInt(): void
    {
        $world = new World(template_dir: __DIR__ . '/fixtures');
        $inspector = new Inspector($world);

        $inspector->inspectFile('hello.typ');

        $cleared = $inspector->clearCache();
        static::assertIsInt($cleared);
        static::assertGreaterThanOrEqual(0, $cleared);
    }
}
