<?php

declare(strict_types=1);

namespace Typst\Tests;

use PHPUnit\Framework\TestCase;
use Typst\Compiler;
use Typst\Exception\ExceptionInterface;
use Typst\Exception\InvalidArgumentException;
use Typst\Exception\LogicException;
use Typst\Exception\OutOfBoundsException;
use Typst\Exception\RuntimeException;
use Typst\World;

final class ExceptionTest extends TestCase
{
    public function testExceptionInterfaceIsInterface(): void
    {
        $r = new \ReflectionClass(ExceptionInterface::class);
        static::assertTrue($r->isInterface());
    }

    public function testExceptionInterfaceExtendsThrowable(): void
    {
        $r = new \ReflectionClass(ExceptionInterface::class);
        static::assertTrue($r->implementsInterface(\Throwable::class));
    }

    public function testRuntimeExceptionExtendsSplRuntimeException(): void
    {
        $r = new \ReflectionClass(RuntimeException::class);
        static::assertTrue($r->isSubclassOf(\RuntimeException::class));
    }

    public function testRuntimeExceptionImplementsExceptionInterface(): void
    {
        $r = new \ReflectionClass(RuntimeException::class);
        static::assertTrue($r->implementsInterface(ExceptionInterface::class));
    }

    public function testLogicExceptionExtendsSplLogicException(): void
    {
        $r = new \ReflectionClass(LogicException::class);
        static::assertTrue($r->isSubclassOf(\LogicException::class));
    }

    public function testLogicExceptionImplementsExceptionInterface(): void
    {
        $r = new \ReflectionClass(LogicException::class);
        static::assertTrue($r->implementsInterface(ExceptionInterface::class));
    }

    public function testInvalidArgumentExceptionExtendsSplInvalidArgumentException(): void
    {
        $r = new \ReflectionClass(InvalidArgumentException::class);
        static::assertTrue($r->isSubclassOf(\InvalidArgumentException::class));
    }

    public function testInvalidArgumentExceptionImplementsExceptionInterface(): void
    {
        $r = new \ReflectionClass(InvalidArgumentException::class);
        static::assertTrue($r->implementsInterface(ExceptionInterface::class));
    }

    public function testInvalidArgumentExceptionIsAlsoLogicException(): void
    {
        $r = new \ReflectionClass(InvalidArgumentException::class);
        static::assertTrue($r->isSubclassOf(\LogicException::class));
    }

    public function testOutOfBoundsExceptionExtendsSplOutOfBoundsException(): void
    {
        $r = new \ReflectionClass(OutOfBoundsException::class);
        static::assertTrue($r->isSubclassOf(\OutOfBoundsException::class));
    }

    public function testOutOfBoundsExceptionImplementsExceptionInterface(): void
    {
        $r = new \ReflectionClass(OutOfBoundsException::class);
        static::assertTrue($r->implementsInterface(ExceptionInterface::class));
    }

    public function testOutOfBoundsExceptionIsAlsoRuntimeException(): void
    {
        $r = new \ReflectionClass(OutOfBoundsException::class);
        static::assertTrue($r->isSubclassOf(\RuntimeException::class));
    }

    public function testCompilationFailureCatchableAsExceptionInterface(): void
    {
        $world = new World();
        $c = new Compiler($world);
        try {
            $source = $world->loadString('#unknown-func()');
            $c->compile($source);
            static::fail('Expected RuntimeException');
        } catch (ExceptionInterface $e) {
            static::assertInstanceOf(RuntimeException::class, $e);
            static::assertInstanceOf(\RuntimeException::class, $e);
        }
    }

    public function testFontFailureCatchableAsExceptionInterface(): void
    {
        $world = new World();
        try {
            $world->addFontData('not a font');
            static::fail('Expected RuntimeException');
        } catch (ExceptionInterface $e) {
            static::assertInstanceOf(RuntimeException::class, $e);
        }
    }

    public function testIOFailureCatchableAsExceptionInterface(): void
    {
        $world = new World();
        try {
            $world->loadFile('/nonexistent/file.typ');
            static::fail('Expected RuntimeException');
        } catch (ExceptionInterface $e) {
            static::assertInstanceOf(RuntimeException::class, $e);
        }
    }

    public function testRuntimeExceptionConstants(): void
    {
        static::assertSame(1, RuntimeException::COMPILATION_FAILED);
        static::assertSame(2, RuntimeException::FILE_NOT_FOUND);
        static::assertSame(3, RuntimeException::WRITE_FAILED);
        static::assertSame(4, RuntimeException::FONT_INVALID);
        static::assertSame(5, RuntimeException::ENCODING_FAILED);
    }

    public function testCompilationFailureHasCorrectCode(): void
    {
        $world = new World();
        $c = new Compiler($world);
        try {
            $source = $world->loadString('#unknown-func()');
            $c->compile($source);
            static::fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            static::assertSame(RuntimeException::COMPILATION_FAILED, $e->getCode());
        }
    }

    public function testFileNotFoundHasCorrectCode(): void
    {
        $world = new World();
        try {
            $world->loadFile('/nonexistent/file.typ');
            static::fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            static::assertSame(RuntimeException::FILE_NOT_FOUND, $e->getCode());
        }
    }

    public function testFontFileNotFoundHasCorrectCode(): void
    {
        $world = new World();
        try {
            $world->addFontFile('/nonexistent/font.ttf');
            static::fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            static::assertSame(RuntimeException::FILE_NOT_FOUND, $e->getCode());
        }
    }

    public function testFontInvalidHasCorrectCode(): void
    {
        $world = new World();
        try {
            $world->addFontData('not a font');
            static::fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            static::assertSame(RuntimeException::FONT_INVALID, $e->getCode());
        }
    }

    public function testWriteFailedHasCorrectCode(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $doc = $c->compile($source);
        $pdf = $doc->toPdf();
        try {
            $pdf->save('/nonexistent/dir/file.pdf');
            static::fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            static::assertSame(RuntimeException::WRITE_FAILED, $e->getCode());
        }
    }

    public function testImageSaveWriteFailedHasCorrectCode(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $doc = $c->compile($source);
        $img = $doc->toImage();
        try {
            $img->save('/nonexistent/dir/file.png');
            static::fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            static::assertSame(RuntimeException::WRITE_FAILED, $e->getCode());
        }
    }

    public function testSvgSaveWriteFailedHasCorrectCode(): void
    {
        $world = new World();
        $c = new Compiler($world);
        $source = $world->loadString("#set page(height: auto)\nHello");
        $doc = $c->compile($source);
        $svg = $doc->toSvg();
        try {
            $svg->save('/nonexistent/dir/file.svg');
            static::fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            static::assertSame(RuntimeException::WRITE_FAILED, $e->getCode());
        }
    }
}
