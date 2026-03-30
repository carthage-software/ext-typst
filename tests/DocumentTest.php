<?php

declare(strict_types=1);

namespace Typst\Tests;

use PHPUnit\Framework\TestCase;
use Typst\Compiler;
use Typst\Document;
use Typst\Exception\InvalidArgumentException;
use Typst\Exception\OutOfBoundsException;
use Typst\ImageFormat;
use Typst\ImageOptions;
use Typst\Output\Image;
use Typst\Output\Pdf;
use Typst\Output\Svg;
use Typst\World;

final class DocumentTest extends TestCase
{
    private static World $world;
    private static Compiler $compiler;

    public static function setUpBeforeClass(): void
    {
        self::$world = new World();
        self::$compiler = new Compiler(self::$world);
    }

    private function compileSimple(string $body = 'Hello'): Document
    {
        $source = self::$world->loadString("#set page(height: auto)\n{$body}");
        return self::$compiler->compile($source);
    }

    public function testPageCountSinglePage(): void
    {
        $doc = $this->compileSimple();
        static::assertSame(1, $doc->pageCount());
    }

    public function testPageCountMultiPage(): void
    {
        $source = self::$world->loadString("Page 1\n#pagebreak()\nPage 2\n#pagebreak()\nPage 3");
        $doc = self::$compiler->compile($source);
        static::assertSame(3, $doc->pageCount());
    }

    public function testPageCountEmptyDocument(): void
    {
        $source = self::$world->loadString('');
        $doc = self::$compiler->compile($source);
        static::assertSame(1, $doc->pageCount());
    }

    public function testToPdfReturnsPdf(): void
    {
        $doc = $this->compileSimple();
        $pdf = $doc->toPdf();
        static::assertInstanceOf(Pdf::class, $pdf);
    }

    public function testToPdfHasContent(): void
    {
        $doc = $this->compileSimple();
        $pdf = $doc->toPdf();
        static::assertGreaterThan(0, $pdf->size());
        static::assertStringStartsWith('%PDF', $pdf->bytes());
    }

    public function testToPdfPageCount(): void
    {
        $source = self::$world->loadString("Page 1\n#pagebreak()\nPage 2");
        $doc = self::$compiler->compile($source);
        $pdf = $doc->toPdf();
        static::assertSame(2, $pdf->pageCount());
    }

    public function testToImageReturnsImage(): void
    {
        $doc = $this->compileSimple();
        $img = $doc->toImage();
        static::assertInstanceOf(Image::class, $img);
    }

    public function testToImageDefaultsPng(): void
    {
        $doc = $this->compileSimple();
        $img = $doc->toImage();
        static::assertSame(ImageFormat::Png, $img->format());
    }

    public function testToImageHasDimensions(): void
    {
        $doc = $this->compileSimple();
        $img = $doc->toImage();
        static::assertGreaterThan(0, $img->width());
        static::assertGreaterThan(0, $img->height());
    }

    public function testToImageWithOptions(): void
    {
        $doc = $this->compileSimple();
        $opts = new ImageOptions(ImageFormat::Png, null, 72.0);
        $img = $doc->toImage(null, $opts);
        static::assertInstanceOf(Image::class, $img);
        static::assertGreaterThan(0, $img->size());
    }

    public function testToImageHigherDpiLargerImage(): void
    {
        $doc = $this->compileSimple();
        $low = $doc->toImage(null, new ImageOptions(null, null, 72.0));
        $high = $doc->toImage(null, new ImageOptions(null, null, 300.0));
        static::assertGreaterThan($low->width(), $high->width());
        static::assertGreaterThan($low->height(), $high->height());
    }

    public function testToImageSpecificPage(): void
    {
        $source = self::$world->loadString("Page 1\n#pagebreak()\nPage 2");
        $doc = self::$compiler->compile($source);
        $img0 = $doc->toImage(0);
        $img1 = $doc->toImage(1);
        static::assertInstanceOf(Image::class, $img0);
        static::assertInstanceOf(Image::class, $img1);
    }

    public function testToImagePageOutOfRangeThrows(): void
    {
        $doc = $this->compileSimple();
        $this->expectException(OutOfBoundsException::class);
        $doc->toImage(999);
    }

    public function testToImageNegativePageThrows(): void
    {
        $doc = $this->compileSimple();
        $this->expectException(InvalidArgumentException::class);
        $doc->toImage(-1);
    }

    public function testToImagesReturnsArray(): void
    {
        $source = self::$world->loadString("Page 1\n#pagebreak()\nPage 2\n#pagebreak()\nPage 3");
        $doc = self::$compiler->compile($source);
        $images = $doc->toImages();
        static::assertCount(3, $images);
        foreach ($images as $img) {
            static::assertInstanceOf(Image::class, $img);
        }
    }

    public function testToImagesSinglePage(): void
    {
        $doc = $this->compileSimple();
        $images = $doc->toImages();
        static::assertCount(1, $images);
    }

    public function testToSvgsReturnsArray(): void
    {
        $source = self::$world->loadString("Page 1\n#pagebreak()\nPage 2\n#pagebreak()\nPage 3");
        $doc = self::$compiler->compile($source);
        $svgs = $doc->toSvgs();
        static::assertCount(3, $svgs);
        foreach ($svgs as $svg) {
            static::assertInstanceOf(Svg::class, $svg);
            static::assertStringContainsString('<svg', $svg->bytes());
        }
    }

    public function testToSvgsSinglePage(): void
    {
        $doc = $this->compileSimple();
        $svgs = $doc->toSvgs();
        static::assertCount(1, $svgs);
    }

    public function testToSvgReturnsSvg(): void
    {
        $doc = $this->compileSimple();
        $svg = $doc->toSvg();
        static::assertInstanceOf(Svg::class, $svg);
    }

    public function testToSvgContainsSvgMarkup(): void
    {
        $doc = $this->compileSimple();
        $svg = $doc->toSvg();
        static::assertStringContainsString('<svg', $svg->bytes());
    }

    public function testToSvgSpecificPage(): void
    {
        $source = self::$world->loadString("Page 1\n#pagebreak()\nPage 2");
        $doc = self::$compiler->compile($source);
        $svg = $doc->toSvg(1);
        static::assertInstanceOf(Svg::class, $svg);
        static::assertStringContainsString('<svg', $svg->bytes());
    }

    public function testToSvgDefaultPage0(): void
    {
        $doc = $this->compileSimple();
        $svg = $doc->toSvg(0);
        static::assertInstanceOf(Svg::class, $svg);
    }

    public function testToSvgPageOutOfRangeThrows(): void
    {
        $doc = $this->compileSimple();
        $this->expectException(OutOfBoundsException::class);
        $doc->toSvg(999);
    }

    public function testToSvgNegativePageThrows(): void
    {
        $doc = $this->compileSimple();
        $this->expectException(InvalidArgumentException::class);
        $doc->toSvg(-1);
    }

    public function testDocumentCanProduceMultipleOutputTypes(): void
    {
        $doc = $this->compileSimple();
        $pdf = $doc->toPdf();
        $img = $doc->toImage();
        $svg = $doc->toSvg();
        static::assertInstanceOf(Pdf::class, $pdf);
        static::assertInstanceOf(Image::class, $img);
        static::assertInstanceOf(Svg::class, $svg);
    }

    public function testDocumentReusableForMultipleCalls(): void
    {
        $doc = $this->compileSimple();
        $pdf1 = $doc->toPdf();
        $pdf2 = $doc->toPdf();
        static::assertSame($pdf1->size(), $pdf2->size());
    }

    public function testToImageWithPageAndOptions(): void
    {
        $source = self::$world->loadString("Page 1\n#pagebreak()\nPage 2");
        $doc = self::$compiler->compile($source);
        $opts = new ImageOptions(ImageFormat::Png, null, 72.0);
        $img = $doc->toImage(1, $opts);
        static::assertInstanceOf(Image::class, $img);
        static::assertGreaterThan(0, $img->size());
    }

    public function testToImagesWithCustomOptions(): void
    {
        $source = self::$world->loadString("Page 1\n#pagebreak()\nPage 2");
        $doc = self::$compiler->compile($source);
        $opts = new ImageOptions(ImageFormat::Png, null, 72.0);
        $images = $doc->toImages($opts);
        static::assertCount(2, $images);
        foreach ($images as $img) {
            static::assertInstanceOf(Image::class, $img);
            static::assertSame(ImageFormat::Png, $img->format());
            static::assertGreaterThan(0, $img->width());
        }
    }

    public function testToImagesWithHighDpi(): void
    {
        $doc = $this->compileSimple();
        $lowDpi = $doc->toImages(new ImageOptions(null, null, 72.0));
        $highDpi = $doc->toImages(new ImageOptions(null, null, 300.0));
        static::assertGreaterThan($lowDpi[0]->width(), $highDpi[0]->width());
    }
}
