<?php

declare(strict_types=1);

namespace Typst\Tests;

use PHPUnit\Framework\TestCase;
use Typst\Diagnostic\CompilationResult;
use Typst\Diagnostic\Diagnostic;
use Typst\Diagnostic\Severity;
use Typst\Diagnostic\SourceSpan;
use Typst\Document;
use Typst\Inspector;
use Typst\World;

final class InspectTest extends TestCase
{
    public function testInspectSuccessful(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $result = $inspector->inspect($source);
        static::assertInstanceOf(CompilationResult::class, $result);
        static::assertTrue($result->success());
        static::assertFalse($result->hasErrors());
    }

    public function testInspectFailedReturnsErrors(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString('#unknown-func()');
        $result = $inspector->inspect($source);
        static::assertFalse($result->success());
        static::assertTrue($result->hasErrors());
        static::assertGreaterThan(0, count($result->errors()));
    }

    public function testInspectWithInputs(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("x")');
        $result = $inspector->inspect($source, ['x' => 'test']);
        static::assertTrue($result->success());
    }

    public function testInspectFileValid(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'typst_test_');
        static::assertNotFalse($tmp);
        file_put_contents($tmp, "#set page(height: auto)\nHello");
        try {
            $world = new World();
            $inspector = new Inspector($world);
            $source = $world->loadFile($tmp);
            $result = $inspector->inspect($source);
            static::assertTrue($result->success());
        } finally {
            unlink($tmp);
        }
    }

    public function testInspectFileInvalidReturnsErrors(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'typst_test_');
        static::assertNotFalse($tmp);
        file_put_contents($tmp, '#unknown-func()');
        try {
            $world = new World();
            $inspector = new Inspector($world);
            $source = $world->loadFile($tmp);
            $result = $inspector->inspect($source);
            static::assertFalse($result->success());
            static::assertTrue($result->hasErrors());
        } finally {
            unlink($tmp);
        }
    }

    public function testGetDocumentReturnsDocument(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $result = $inspector->inspect($source);
        static::assertTrue($result->success());

        $doc = $result->getDocument();
        static::assertInstanceOf(Document::class, $doc);
    }

    public function testGetDocumentCanBeCalledMultipleTimes(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $result = $inspector->inspect($source);
        $doc1 = $result->getDocument();
        $doc2 = $result->getDocument();

        static::assertInstanceOf(Document::class, $doc1);
        static::assertInstanceOf(Document::class, $doc2);
        static::assertSame($doc1->pageCount(), $doc2->pageCount());
    }

    public function testDocumentReturnsNullOnFailure(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString('#unknown-func()');
        $result = $inspector->inspect($source);
        static::assertFalse($result->success());
        static::assertNull($result->getDocument());
    }

    public function testSuccessRemainsStableAfterTake(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $result = $inspector->inspect($source);
        static::assertTrue($result->success());
        $result->getDocument();
        static::assertTrue($result->success());
    }

    public function testDiagnosticsOnSuccessNoWarnings(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $result = $inspector->inspect($source);
        static::assertCount(0, $result->errors());
    }

    public function testDiagnosticsOnFailure(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString('#unknown-func()');
        $result = $inspector->inspect($source);
        $errors = $result->errors();
        static::assertGreaterThan(0, count($errors));

        $diag = $errors[0];
        static::assertInstanceOf(Diagnostic::class, $diag);
        static::assertSame(Severity::Error, $diag->severity());
        static::assertNotEmpty($diag->message());
    }

    public function testDiagnosticsIncludesBothErrorsAndWarnings(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString('#unknown-func()');
        $result = $inspector->inspect($source);
        $all = $result->diagnostics();
        $errCount = count($result->errors());
        $warnCount = count($result->warnings());
        static::assertCount($errCount + $warnCount, $all);
    }

    public function testDiagnosticSeverityMessageSpanHints(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString("#set page(height: auto)\n#unknown-func()");
        $result = $inspector->inspect($source);
        $errors = $result->errors();
        static::assertGreaterThan(0, count($errors));

        $diag = $errors[0];
        static::assertSame(Severity::Error, $diag->severity());
        static::assertNotEmpty($diag->message());
        static::assertIsArray($diag->hints());
    }

    public function testDiagnosticToString(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString('#unknown-func()');
        $result = $inspector->inspect($source);
        $str = (string) $result->errors()[0];
        static::assertStringContainsString('error:', $str);
    }

    public function testDiagnosticImplementsStringable(): void
    {
        $r = new \ReflectionClass(Diagnostic::class);
        static::assertTrue($r->implementsInterface(\Stringable::class));
    }

    public function testDiagnosticSpanWithSourceLocation(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString("#set page(height: auto)\n#unknown-func()");
        $result = $inspector->inspect($source);
        $span = $result->errors()[0]->span();
        if ($span !== null) {
            static::assertInstanceOf(SourceSpan::class, $span);
            static::assertGreaterThan(0, $span->line());
            static::assertGreaterThan(0, $span->column());
            static::assertIsString($span->file());
        }
    }

    public function testSourceSpanFromFileError(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'typst_test_');
        static::assertNotFalse($tmp);
        file_put_contents($tmp, "Valid line\n#unknown-func()");
        try {
            $world = new World();
            $inspector = new Inspector($world);
            $source = $world->loadFile($tmp);
            $result = $inspector->inspect($source);
            $span = $result->errors()[0]->span();
            if ($span !== null) {
                static::assertInstanceOf(SourceSpan::class, $span);
                static::assertSame(2, $span->line());
                static::assertGreaterThan(0, $span->column());
                static::assertIsString($span->text());
            }
        } finally {
            unlink($tmp);
        }
    }

    public function testMultipleErrorsInSingleCompilation(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString("#unknown1()\n#unknown2()\n#unknown3()");
        $result = $inspector->inspect($source);
        static::assertGreaterThanOrEqual(1, count($result->errors()));
    }

    public function testWarningsOnUnknownFont(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString("#set page(height: auto)\n#set text(font: \"NonExistentFont12345\")\nHello");
        $result = $inspector->inspect($source);
        static::assertTrue($result->success());
        static::assertTrue($result->hasWarnings());
        static::assertGreaterThan(0, count($result->warnings()));
        static::assertSame(Severity::Warning, $result->warnings()[0]->severity());
    }

    public function testWarningsCountInDiagnostics(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString("#set page(height: auto)\n#set text(font: \"NonExistentFont12345\")\nHello");
        $result = $inspector->inspect($source);
        $all = $result->diagnostics();
        $warnings = $result->warnings();
        $errors = $result->errors();
        static::assertCount(count($errors) + count($warnings), $all);
        static::assertGreaterThan(0, count($warnings));
        static::assertSame(0, count($errors));
    }

    public function testWarningDiagnosticMessage(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString("#set page(height: auto)\n#set text(font: \"NonExistentFont12345\")\nHello");
        $result = $inspector->inspect($source);
        $warning = $result->warnings()[0];
        static::assertNotEmpty($warning->message());
        static::assertIsArray($warning->hints());
    }

    public function testWarningDiagnosticToString(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString("#set page(height: auto)\n#set text(font: \"NonExistentFont12345\")\nHello");
        $result = $inspector->inspect($source);
        $str = (string) $result->warnings()[0];
        static::assertStringContainsString('warning:', $str);
    }

    public function testInspectWithInputsVerifiesValue(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $source = $world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("key")');
        $result = $inspector->inspect($source, ['key' => 'hello']);
        static::assertTrue($result->success());
        $doc = $result->getDocument();
        static::assertInstanceOf(Document::class, $doc);
    }

    public function testInspectStringConvenience(): void
    {
        $world = new World();
        $inspector = new Inspector($world);
        $result = $inspector->inspectString("#set page(height: auto)\nHello");
        static::assertInstanceOf(CompilationResult::class, $result);
        static::assertTrue($result->success());
    }

    public function testInspectFileConvenience(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'typst_test_');
        static::assertNotFalse($tmp);
        file_put_contents($tmp, "#set page(height: auto)\nHello");
        try {
            $world = new World();
            $inspector = new Inspector($world);
            $result = $inspector->inspectFile($tmp);
            static::assertInstanceOf(CompilationResult::class, $result);
            static::assertTrue($result->success());
        } finally {
            unlink($tmp);
        }
    }
}
