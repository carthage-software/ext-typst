<?php

declare(strict_types=1);

namespace Typst\Tests;

use PHPUnit\Framework\TestCase;
use Typst\Compiler;
use Typst\Diagnostic\CompilationResult;
use Typst\Diagnostic\Diagnostic;
use Typst\Diagnostic\Severity;
use Typst\Diagnostic\SourceSpan;
use Typst\Document;
use Typst\ImageFormat;
use Typst\ImageOptions;
use Typst\Inspector;
use Typst\Output\Image;
use Typst\Output\OutputInterface;
use Typst\Output\Pdf;
use Typst\Output\Svg;
use Typst\PdfOptions;
use Typst\PdfValidator;
use Typst\PdfVersion;
use Typst\Source;
use Typst\World;

final class ReflectionTest extends TestCase
{
    public function testCompilerIsFinal(): void
    {
        $r = new \ReflectionClass(Compiler::class);
        static::assertTrue($r->isFinal());
    }

    public function testWorldIsFinal(): void
    {
        $r = new \ReflectionClass(World::class);
        static::assertTrue($r->isFinal());
    }

    public function testInspectorIsFinal(): void
    {
        $r = new \ReflectionClass(Inspector::class);
        static::assertTrue($r->isFinal());
    }

    public function testSourceIsFinal(): void
    {
        $r = new \ReflectionClass(Source::class);
        static::assertTrue($r->isFinal());
    }

    public function testDocumentIsFinal(): void
    {
        $r = new \ReflectionClass(Document::class);
        static::assertTrue($r->isFinal());
    }

    public function testCompilationResultIsFinal(): void
    {
        $r = new \ReflectionClass(CompilationResult::class);
        static::assertTrue($r->isFinal());
    }

    public function testDiagnosticIsFinal(): void
    {
        $r = new \ReflectionClass(Diagnostic::class);
        static::assertTrue($r->isFinal());
    }

    public function testSourceSpanIsFinal(): void
    {
        $r = new \ReflectionClass(SourceSpan::class);
        static::assertTrue($r->isFinal());
    }

    public function testImageOptionsIsFinal(): void
    {
        $r = new \ReflectionClass(ImageOptions::class);
        static::assertTrue($r->isFinal());
    }

    public function testPdfIsFinal(): void
    {
        $r = new \ReflectionClass(Pdf::class);
        static::assertTrue($r->isFinal());
    }

    public function testImageIsFinal(): void
    {
        $r = new \ReflectionClass(Image::class);
        static::assertTrue($r->isFinal());
    }

    public function testSvgIsFinal(): void
    {
        $r = new \ReflectionClass(Svg::class);
        static::assertTrue($r->isFinal());
    }

    public function testPdfImplementsOutputInterface(): void
    {
        $r = new \ReflectionClass(Pdf::class);
        static::assertTrue($r->implementsInterface(OutputInterface::class));
    }

    public function testImageImplementsOutputInterface(): void
    {
        $r = new \ReflectionClass(Image::class);
        static::assertTrue($r->implementsInterface(OutputInterface::class));
    }

    public function testSvgImplementsOutputInterface(): void
    {
        $r = new \ReflectionClass(Svg::class);
        static::assertTrue($r->implementsInterface(OutputInterface::class));
    }

    public function testOutputInterfaceMethods(): void
    {
        $r = new \ReflectionClass(OutputInterface::class);
        $methods = array_map(static fn(\ReflectionMethod $m) => $m->getName(), $r->getMethods());

        static::assertContains('bytes', $methods);
        static::assertContains('size', $methods);
        static::assertContains('save', $methods);
        static::assertContains('__toString', $methods);
    }

    public function testOutputInterfaceExtendsStringable(): void
    {
        $r = new \ReflectionClass(OutputInterface::class);
        static::assertTrue($r->implementsInterface(\Stringable::class));
    }

    public function testDocumentMethods(): void
    {
        $r = new \ReflectionClass(Document::class);
        $methods = array_map(static fn(\ReflectionMethod $m) => $m->getName(), $r->getMethods());

        static::assertContains('pageCount', $methods);
        static::assertContains('pageWidth', $methods);
        static::assertContains('pageHeight', $methods);
        static::assertContains('toPdf', $methods);
        static::assertContains('toImage', $methods);
        static::assertContains('toImages', $methods);
        static::assertContains('toSvg', $methods);
        static::assertContains('toSvgs', $methods);
    }

    public function testCompilationResultMethods(): void
    {
        $r = new \ReflectionClass(CompilationResult::class);
        $methods = array_map(static fn(\ReflectionMethod $m) => $m->getName(), $r->getMethods());

        static::assertContains('getDocument', $methods);
        static::assertContains('success', $methods);
        static::assertContains('diagnostics', $methods);
        static::assertContains('warnings', $methods);
        static::assertContains('errors', $methods);
        static::assertContains('hasWarnings', $methods);
        static::assertContains('hasErrors', $methods);
    }

    public function testDiagnosticMethods(): void
    {
        $r = new \ReflectionClass(Diagnostic::class);
        $methods = array_map(static fn(\ReflectionMethod $m) => $m->getName(), $r->getMethods());

        static::assertContains('severity', $methods);
        static::assertContains('message', $methods);
        static::assertContains('span', $methods);
        static::assertContains('hints', $methods);
        static::assertContains('__toString', $methods);
    }

    public function testSourceSpanMethods(): void
    {
        $r = new \ReflectionClass(SourceSpan::class);
        $methods = array_map(static fn(\ReflectionMethod $m) => $m->getName(), $r->getMethods());

        static::assertContains('file', $methods);
        static::assertContains('line', $methods);
        static::assertContains('column', $methods);
        static::assertContains('text', $methods);
    }

    public function testSourceMethods(): void
    {
        $r = new \ReflectionClass(Source::class);
        $methods = array_map(static fn(\ReflectionMethod $m) => $m->getName(), $r->getMethods());

        static::assertContains('getId', $methods);
        static::assertContains('getText', $methods);
    }

    public function testWorldMethods(): void
    {
        $r = new \ReflectionClass(World::class);
        $methods = array_map(static fn(\ReflectionMethod $m) => $m->getName(), $r->getMethods());

        static::assertContains('loadString', $methods);
        static::assertContains('loadFile', $methods);
        static::assertContains('addFontFile', $methods);
        static::assertContains('addFontData', $methods);
        static::assertContains('getFontFamilies', $methods);
    }

    public function testInspectorMethods(): void
    {
        $r = new \ReflectionClass(Inspector::class);
        $methods = array_map(static fn(\ReflectionMethod $m) => $m->getName(), $r->getMethods());

        static::assertContains('inspect', $methods);
        static::assertContains('inspectString', $methods);
        static::assertContains('inspectFile', $methods);
        static::assertContains('clearCache', $methods);
    }

    public function testCompilerMethods(): void
    {
        $r = new \ReflectionClass(Compiler::class);
        $methods = array_map(static fn(\ReflectionMethod $m) => $m->getName(), $r->getMethods());

        static::assertContains('compile', $methods);
        static::assertContains('compileString', $methods);
        static::assertContains('compileFile', $methods);
        static::assertContains('clearCache', $methods);
        static::assertContains('getWorld', $methods);
        static::assertNotContains('inspect', $methods);
        static::assertNotContains('addFontFile', $methods);
        static::assertNotContains('addFontData', $methods);
        static::assertNotContains('version', $methods);
        static::assertNotContains('typstVersion', $methods);
    }

    public function testPdfOptionsIsFinal(): void
    {
        $r = new \ReflectionClass(PdfOptions::class);
        static::assertTrue($r->isFinal());
    }

    public function testSeverityIsEnum(): void
    {
        static::assertTrue(enum_exists(Severity::class));
    }

    public function testImageFormatIsEnum(): void
    {
        static::assertTrue(enum_exists(ImageFormat::class));
    }

    public function testPdfVersionIsEnum(): void
    {
        static::assertTrue(enum_exists(PdfVersion::class));
    }

    public function testPdfValidatorIsEnum(): void
    {
        static::assertTrue(enum_exists(PdfValidator::class));
    }

    public function testOutputInterfaceIsInterface(): void
    {
        $r = new \ReflectionClass(OutputInterface::class);
        static::assertTrue($r->isInterface());
    }

    public function testImageMethods(): void
    {
        $r = new \ReflectionClass(Image::class);
        $methods = array_map(static fn(\ReflectionMethod $m) => $m->getName(), $r->getMethods());

        static::assertContains('bytes', $methods);
        static::assertContains('size', $methods);
        static::assertContains('format', $methods);
        static::assertContains('width', $methods);
        static::assertContains('height', $methods);
        static::assertContains('save', $methods);
        static::assertContains('__toString', $methods);
    }

    public function testPdfMethods(): void
    {
        $r = new \ReflectionClass(Pdf::class);
        $methods = array_map(static fn(\ReflectionMethod $m) => $m->getName(), $r->getMethods());

        static::assertContains('bytes', $methods);
        static::assertContains('size', $methods);
        static::assertContains('pageCount', $methods);
        static::assertContains('save', $methods);
        static::assertContains('__toString', $methods);
    }

    public function testSvgMethods(): void
    {
        $r = new \ReflectionClass(Svg::class);
        $methods = array_map(static fn(\ReflectionMethod $m) => $m->getName(), $r->getMethods());

        static::assertContains('bytes', $methods);
        static::assertContains('size', $methods);
        static::assertContains('save', $methods);
        static::assertContains('__toString', $methods);
    }

    public function testAllClassesExist(): void
    {
        $classes = [
            Compiler::class,
            World::class,
            Inspector::class,
            Source::class,
            Document::class,
            CompilationResult::class,
            Diagnostic::class,
            SourceSpan::class,
            ImageOptions::class,
            PdfOptions::class,
            Pdf::class,
            Image::class,
            Svg::class,
            OutputInterface::class,
        ];

        foreach ($classes as $class) {
            static::assertTrue(class_exists($class) || interface_exists($class), "Class {$class} should exist");
        }
    }

    public function testRemovedClassesDoNotExist(): void
    {
        static::assertFalse(class_exists('Typst\CompilerOptions'));
        static::assertFalse(interface_exists('Typst\CompilerInterface'));
        static::assertFalse(class_exists('Typst\SourceResolver'));
        static::assertFalse(class_exists('Typst\FontManager'));
    }

    public function testAllEnumsExist(): void
    {
        static::assertTrue(enum_exists(Severity::class));
        static::assertTrue(enum_exists(ImageFormat::class));
        static::assertTrue(enum_exists(PdfVersion::class));
        static::assertTrue(enum_exists(PdfValidator::class));
    }

    public function testStandaloneFunctionsExist(): void
    {
        static::assertTrue(function_exists('Typst\version'));
        static::assertTrue(function_exists('Typst\typst_version'));
    }

    public function testPdfImplementsStringable(): void
    {
        $r = new \ReflectionClass(Pdf::class);
        static::assertTrue($r->implementsInterface(\Stringable::class));
    }

    public function testImageImplementsStringable(): void
    {
        $r = new \ReflectionClass(Image::class);
        static::assertTrue($r->implementsInterface(\Stringable::class));
    }

    public function testSvgImplementsStringable(): void
    {
        $r = new \ReflectionClass(Svg::class);
        static::assertTrue($r->implementsInterface(\Stringable::class));
    }

    public function testDiagnosticImplementsStringable(): void
    {
        $r = new \ReflectionClass(Diagnostic::class);
        static::assertTrue($r->implementsInterface(\Stringable::class));
    }

    public function testCompilerIsCloneable(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $clone = clone $c;
        static::assertInstanceOf(Compiler::class, $clone);
    }

    public function testWorldIsCloneable(): void
    {
        $world = new World();
        $clone = clone $world;
        static::assertInstanceOf(World::class, $clone);
    }

    public function testInspectorIsCloneable(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $clone = clone $inspector;
        static::assertInstanceOf(Inspector::class, $clone);
    }

    public function testDocumentNotCloneable(): void
    {
        $world = new World();
        $source = $world->loadString("#set page(height: auto)\nHello");
        $doc = (new Compiler($world))->compile($source);
        $this->expectException(\Error::class);
        clone $doc;
    }

    public function testPdfNotCloneable(): void
    {
        $world = new World();
        $source = $world->loadString("#set page(height: auto)\nHello");
        $pdf = (new Compiler($world))
            ->compile($source)
            ->toPdf();
        $this->expectException(\Error::class);
        clone $pdf;
    }

    public function testImageNotCloneable(): void
    {
        $world = new World();
        $source = $world->loadString("#set page(height: auto)\nHello");
        $img = (new Compiler($world))
            ->compile($source)
            ->toImage();
        $this->expectException(\Error::class);
        clone $img;
    }

    public function testSvgNotCloneable(): void
    {
        $world = new World();
        $source = $world->loadString("#set page(height: auto)\nHello");
        $svg = (new Compiler($world))
            ->compile($source)
            ->toSvg();
        $this->expectException(\Error::class);
        clone $svg;
    }

    public function testCompilationResultNotCloneable(): void
    {
        $world = new World();
        $source = $world->loadString("#set page(height: auto)\nHello");
        $result = (new Inspector($world))->inspect($source);
        $this->expectException(\Error::class);
        clone $result;
    }

    public function testSourceNotCloneable(): void
    {
        $world = new World();
        $source = $world->loadString("#set page(height: auto)\nHello");
        $this->expectException(\Error::class);
        clone $source;
    }

    public function testPendingDocumentNotCloneable(): void
    {
        $world = new World();
        $compiler = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $pending = $compiler->compileInBackground($source);
        $this->expectException(\Error::class);
        clone $pending;
    }

    public function testImageOptionsCloneable(): void
    {
        $opts = new ImageOptions(dpi: 300.0);
        $clone = clone $opts;
        static::assertSame(300.0, $clone->dpi);
    }

    public function testPdfOptionsNotCloneable(): void
    {
        $opts = new PdfOptions();
        $this->expectException(\Error::class);
        clone $opts;
    }
}
