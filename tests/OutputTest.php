<?php

declare(strict_types=1);

namespace Typst\Tests;

use PHPUnit\Framework\TestCase;
use Typst\Compiler;
use Typst\Exception\InvalidArgumentException;
use Typst\Exception\OutOfBoundsException;
use Typst\Exception\RuntimeException;
use Typst\ImageFormat;
use Typst\Output\Image;
use Typst\Output\OutputInterface;
use Typst\Output\Pdf;
use Typst\Output\Svg;
use Typst\World;

final class OutputTest extends TestCase
{
    private static World $world;
    private static Compiler $compiler;

    public static function setUpBeforeClass(): void
    {
        self::$world = new World();
        self::$compiler = new Compiler(self::$world);
    }

    public function testPdfBytes(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        $bytes = $pdf->bytes();
        static::assertStringStartsWith('%PDF', $bytes);
    }

    public function testPdfSize(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        static::assertSame(strlen($pdf->bytes()), $pdf->size());
    }

    public function testPdfPageCount(): void
    {
        $source = self::$world->loadString("P1\n#pagebreak()\nP2");
        $pdf = self::$compiler->compile($source)->toPdf();
        static::assertSame(2, $pdf->pageCount());
    }

    public function testPdfToString(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        static::assertSame($pdf->bytes(), (string) $pdf);
    }

    public function testPdfImplementsStringable(): void
    {
        $r = new \ReflectionClass(Pdf::class);
        static::assertTrue($r->implementsInterface(\Stringable::class));
    }

    public function testPdfSave(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        $base = tempnam(sys_get_temp_dir(), 'typst_test_');
        static::assertNotFalse($base);
        $tmp = $base . '.pdf';
        try {
            $pdf->save($tmp);
            static::assertFileExists($tmp);
            static::assertSame($pdf->bytes(), file_get_contents($tmp));
        } finally {
            @unlink($base);
            @unlink($tmp);
        }
    }

    public function testPdfSaveInvalidPathThrowsIOException(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        $this->expectException(RuntimeException::class);
        $pdf->save('/nonexistent/dir/file.pdf');
    }

    public function testPdfImplementsOutputInterface(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        static::assertInstanceOf(OutputInterface::class, $pdf);
    }

    public function testImageBytes(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $img = self::$compiler->compile($source)->toImage();
        $bytes = $img->bytes();
        static::assertGreaterThan(0, strlen($bytes));
    }

    public function testImagePngSignature(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $img = self::$compiler->compile($source)->toImage();
        static::assertStringStartsWith("\x89PNG", $img->bytes());
    }

    public function testImageSize(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $img = self::$compiler->compile($source)->toImage();
        static::assertSame(strlen($img->bytes()), $img->size());
    }

    public function testImageFormat(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $img = self::$compiler->compile($source)->toImage();
        static::assertSame(ImageFormat::Png, $img->format());
    }

    public function testImageDimensions(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $img = self::$compiler->compile($source)->toImage();
        static::assertGreaterThan(0, $img->width());
        static::assertGreaterThan(0, $img->height());
    }

    public function testImageToString(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $img = self::$compiler->compile($source)->toImage();
        static::assertSame($img->bytes(), (string) $img);
    }

    public function testImageImplementsStringable(): void
    {
        $r = new \ReflectionClass(Image::class);
        static::assertTrue($r->implementsInterface(\Stringable::class));
    }

    public function testImageSave(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $img = self::$compiler->compile($source)->toImage();
        $base = tempnam(sys_get_temp_dir(), 'typst_test_');
        static::assertNotFalse($base);
        $tmp = $base . '.png';
        try {
            $img->save($tmp);
            static::assertFileExists($tmp);
            static::assertSame($img->bytes(), file_get_contents($tmp));
        } finally {
            @unlink($base);
            @unlink($tmp);
        }
    }

    public function testImageSaveInvalidPathThrowsIOException(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $img = self::$compiler->compile($source)->toImage();
        $this->expectException(RuntimeException::class);
        $img->save('/nonexistent/dir/file.png');
    }

    public function testImageImplementsOutputInterface(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $img = self::$compiler->compile($source)->toImage();
        static::assertInstanceOf(OutputInterface::class, $img);
    }

    public function testSvgBytes(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $svg = self::$compiler->compile($source)->toSvg();
        $bytes = $svg->bytes();
        static::assertStringContainsString('<svg', $bytes);
    }

    public function testSvgSize(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $svg = self::$compiler->compile($source)->toSvg();
        static::assertSame(strlen($svg->bytes()), $svg->size());
    }

    public function testSvgToString(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $svg = self::$compiler->compile($source)->toSvg();
        static::assertSame($svg->bytes(), (string) $svg);
    }

    public function testSvgImplementsStringable(): void
    {
        $r = new \ReflectionClass(Svg::class);
        static::assertTrue($r->implementsInterface(\Stringable::class));
    }

    public function testSvgSave(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $svg = self::$compiler->compile($source)->toSvg();
        $base = tempnam(sys_get_temp_dir(), 'typst_test_');
        static::assertNotFalse($base);
        $tmp = $base . '.svg';
        try {
            $svg->save($tmp);
            static::assertFileExists($tmp);
            static::assertSame($svg->bytes(), file_get_contents($tmp));
        } finally {
            @unlink($base);
            @unlink($tmp);
        }
    }

    public function testSvgSaveInvalidPathThrowsIOException(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $svg = self::$compiler->compile($source)->toSvg();
        $this->expectException(RuntimeException::class);
        $svg->save('/nonexistent/dir/file.svg');
    }

    public function testSvgImplementsOutputInterface(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $svg = self::$compiler->compile($source)->toSvg();
        static::assertInstanceOf(OutputInterface::class, $svg);
    }

    public function testPdfBytesNoArgsReturnsFullData(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        $full = $pdf->bytes();
        static::assertSame($full, $pdf->bytes(null, null));
    }

    public function testPdfBytesOffsetZeroReturnsFullData(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        static::assertSame($pdf->bytes(), $pdf->bytes(offset: 0));
    }

    public function testPdfBytesWithOffset(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        $full = $pdf->bytes();
        static::assertSame(substr($full, 5), $pdf->bytes(offset: 5));
    }

    public function testPdfBytesWithOffsetAndLimit(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        $full = $pdf->bytes();
        static::assertSame(substr($full, 0, 10), $pdf->bytes(offset: 0, limit: 10));
    }

    public function testPdfBytesWithOffsetAndLimitMiddle(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        $full = $pdf->bytes();
        static::assertSame(substr($full, 5, 10), $pdf->bytes(offset: 5, limit: 10));
    }

    public function testPdfBytesLimitOnly(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        $full = $pdf->bytes();
        static::assertSame(substr($full, 0, 10), $pdf->bytes(limit: 10));
    }

    public function testPdfBytesLimitExceedingClampsToEnd(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        $full = $pdf->bytes();
        $offset = $pdf->size() - 5;
        static::assertSame(substr($full, $offset), $pdf->bytes(offset: $offset, limit: 9999));
    }

    public function testPdfBytesOffsetBeyondSizeThrows(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        $this->expectException(OutOfBoundsException::class);
        $pdf->bytes(offset: $pdf->size() + 1);
    }

    public function testPdfBytesNegativeOffsetThrows(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        $this->expectException(InvalidArgumentException::class);
        $pdf->bytes(offset: -1);
    }

    public function testPdfBytesNegativeLimitThrows(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $pdf = self::$compiler->compile($source)->toPdf();
        $this->expectException(InvalidArgumentException::class);
        $pdf->bytes(limit: -1);
    }

    public function testAllOutputTypesShareInterface(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $doc = self::$compiler->compile($source);
        $outputs = [
            $doc->toPdf(),
            $doc->toImage(),
            $doc->toSvg(),
        ];

        foreach ($outputs as $output) {
            static::assertInstanceOf(OutputInterface::class, $output);
            static::assertGreaterThan(0, $output->size());
            static::assertIsString($output->bytes());
        }
    }

    public function testOutputInterfacePolymorphicSave(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $doc = self::$compiler->compile($source);
        $outputs = [
            'pdf' => $doc->toPdf(),
            'png' => $doc->toImage(),
            'svg' => $doc->toSvg(),
        ];

        foreach ($outputs as $ext => $output) {
            $base = tempnam(sys_get_temp_dir(), 'typst_test_');
            static::assertNotFalse($base);
            $tmp = $base . ".{$ext}";
            try {
                $output->save($tmp);
                static::assertFileExists($tmp);
                static::assertSame($output->bytes(), file_get_contents($tmp));
            } finally {
                @unlink($base);
                @unlink($tmp);
            }
        }
    }
}
