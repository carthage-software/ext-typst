<?php

declare(strict_types=1);

namespace Typst\Tests;

use PHPUnit\Framework\TestCase;
use Typst\Exception\InvalidArgumentException;
use Typst\ImageFormat;
use Typst\ImageOptions;
use Typst\PdfOptions;
use Typst\PdfValidator;
use Typst\PdfVersion;

final class OptionsTest extends TestCase
{
    public function testImageOptionsDefault(): void
    {
        $opts = new ImageOptions();
        static::assertSame(ImageFormat::Png, $opts->format);
        static::assertSame(85, $opts->quality);
        static::assertSame(144.0, $opts->dpi);
    }

    public function testImageOptionsAllParameters(): void
    {
        $opts = new ImageOptions(ImageFormat::Png, 90, 300.0);
        static::assertSame(ImageFormat::Png, $opts->format);
        static::assertSame(90, $opts->quality);
        static::assertSame(300.0, $opts->dpi);
    }

    public function testImageOptionsMinQuality(): void
    {
        $opts = new ImageOptions(null, 1);
        static::assertSame(1, $opts->quality);
    }

    public function testImageOptionsMaxQuality(): void
    {
        $opts = new ImageOptions(null, 100);
        static::assertSame(100, $opts->quality);
    }

    public function testImageOptionsQualityZeroThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        // @mago-expect analyzer:invalid-argument
        new ImageOptions(null, 0);
    }

    public function testImageOptionsQuality101Throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        // @mago-expect analyzer:invalid-argument
        new ImageOptions(null, 101);
    }

    public function testImageOptionsNegativeQualityThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        // @mago-expect analyzer:invalid-argument
        new ImageOptions(null, -1);
    }

    public function testImageOptionsDpiZeroThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ImageOptions(null, null, 0.0);
    }

    public function testImageOptionsNegativeDpiThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ImageOptions(null, null, -1.0);
    }

    public function testImageOptionsNanDpiThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ImageOptions(null, null, NAN);
    }

    public function testImageOptionsInfDpiThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ImageOptions(null, null, INF);
    }

    public function testImageOptionsNegativeInfDpiThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ImageOptions(null, null, -INF);
    }

    public function testImageOptionsWithFormat(): void
    {
        $opts = new ImageOptions();
        $new = $opts->withFormat(ImageFormat::Jpeg);
        static::assertSame(ImageFormat::Jpeg, $new->format);
        static::assertSame(ImageFormat::Png, $opts->format);
    }

    public function testImageOptionsWithQuality(): void
    {
        $opts = new ImageOptions();
        $new = $opts->withQuality(50);
        static::assertSame(50, $new->quality);
        static::assertSame(85, $opts->quality);
    }

    public function testImageOptionsWithQualityZeroThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        // @mago-expect analyzer:invalid-argument
        (new ImageOptions())->withQuality(0);
    }

    public function testImageOptionsWithQuality101Throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        // @mago-expect analyzer:invalid-argument
        (new ImageOptions())->withQuality(101);
    }

    public function testImageOptionsWithDpi(): void
    {
        $opts = new ImageOptions();
        $new = $opts->withDpi(72.0);
        static::assertSame(72.0, $new->dpi);
        static::assertSame(144.0, $opts->dpi);
    }

    public function testImageOptionsWithDpiZeroThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ImageOptions())->withDpi(0.0);
    }

    public function testImageOptionsWithDpiNegativeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ImageOptions())->withDpi(-1.0);
    }

    public function testImageOptionsChaining(): void
    {
        $opts = (new ImageOptions())
            ->withFormat(ImageFormat::Jpeg)
            ->withQuality(95)
            ->withDpi(200.0);

        static::assertSame(ImageFormat::Jpeg, $opts->format);
        static::assertSame(95, $opts->quality);
        static::assertSame(200.0, $opts->dpi);
    }

    public function testImageOptionsPropertiesReadOnly(): void
    {
        $opts = new ImageOptions();
        $this->expectException(\Exception::class);
        // @mago-expect analyzer:invalid-property-write
        $opts->quality = 50;
    }

    public function testPdfOptionsDefault(): void
    {
        $opts = new PdfOptions();
        static::assertNull($opts->identifier);
        static::assertNull($opts->timestamp);
        static::assertNull($opts->firstPage);
        static::assertNull($opts->lastPage);
        static::assertNull($opts->version);
        static::assertNull($opts->validator);
        static::assertTrue($opts->tagged);
    }

    public function testPdfOptionsAllParameters(): void
    {
        $opts = new PdfOptions(
            identifier: 'my-doc',
            timestamp: 1_700_000_000,
            first_page: 0,
            last_page: 5,
            version: PdfVersion::V17,
            validator: PdfValidator::A2B,
            tagged: false,
        );
        static::assertSame('my-doc', $opts->identifier);
        static::assertSame(1_700_000_000, $opts->timestamp);
        static::assertSame(0, $opts->firstPage);
        static::assertSame(5, $opts->lastPage);
        static::assertSame(PdfVersion::V17, $opts->version);
        static::assertSame(PdfValidator::A2B, $opts->validator);
        static::assertFalse($opts->tagged);
    }

    public function testPdfOptionsNegativeFirstPageThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('non-negative');
        new PdfOptions(first_page: -1); // @mago-expect analysis:invalid-argument - verifying API
    }

    public function testPdfOptionsNegativeLastPageThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('non-negative');
        new PdfOptions(last_page: -1); // @mago-expect analysis:invalid-argument - verifying API
    }

    public function testPdfOptionsFirstPageGreaterThanLastPageThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must not be greater');
        new PdfOptions(first_page: 5, last_page: 2);
    }

    public function testPdfOptionsWithIdentifier(): void
    {
        $opts = new PdfOptions();
        $new = $opts->withIdentifier('test-id');
        static::assertSame('test-id', $new->identifier);
        static::assertNull($opts->identifier);
    }

    public function testPdfOptionsWithTimestamp(): void
    {
        $opts = new PdfOptions();
        $new = $opts->withTimestamp(1_700_000_000);
        static::assertSame(1_700_000_000, $new->timestamp);
        static::assertNull($opts->timestamp);
    }

    public function testPdfOptionsWithFirstPage(): void
    {
        $opts = new PdfOptions();
        $new = $opts->withFirstPage(2);
        static::assertSame(2, $new->firstPage);
        static::assertNull($opts->firstPage);
    }

    public function testPdfOptionsWithLastPage(): void
    {
        $opts = new PdfOptions();
        $new = $opts->withLastPage(10);
        static::assertSame(10, $new->lastPage);
        static::assertNull($opts->lastPage);
    }

    public function testPdfOptionsWithFirstPageExceedingLastPageThrows(): void
    {
        $opts = new PdfOptions(last_page: 5);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must not be greater');
        $opts->withFirstPage(10);
    }

    public function testPdfOptionsWithLastPageBelowFirstPageThrows(): void
    {
        $opts = new PdfOptions(first_page: 5);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must not be greater');
        $opts->withLastPage(2);
    }

    public function testPdfOptionsWithVersion(): void
    {
        $opts = new PdfOptions();
        $new = $opts->withVersion(PdfVersion::V20);
        static::assertSame(PdfVersion::V20, $new->version);
        static::assertNull($opts->version);
    }

    public function testPdfOptionsWithValidator(): void
    {
        $opts = new PdfOptions();
        $new = $opts->withValidator(PdfValidator::A2B);
        static::assertSame(PdfValidator::A2B, $new->validator);
        static::assertNull($opts->validator);
    }

    public function testPdfOptionsWithTagged(): void
    {
        $opts = new PdfOptions();
        $new = $opts->withTagged(false);
        static::assertFalse($new->tagged);
        static::assertTrue($opts->tagged);
    }

    public function testPdfOptionsChaining(): void
    {
        $opts = (new PdfOptions())
            ->withIdentifier('my-doc')
            ->withTimestamp(1_700_000_000)
            ->withFirstPage(2)
            ->withLastPage(10)
            ->withVersion(PdfVersion::V20)
            ->withValidator(PdfValidator::A4)
            ->withTagged(false);

        static::assertSame('my-doc', $opts->identifier);
        static::assertSame(1_700_000_000, $opts->timestamp);
        static::assertSame(2, $opts->firstPage);
        static::assertSame(10, $opts->lastPage);
        static::assertSame(PdfVersion::V20, $opts->version);
        static::assertSame(PdfValidator::A4, $opts->validator);
        static::assertFalse($opts->tagged);
    }

    public function testPdfOptionsPropertiesReadOnly(): void
    {
        $opts = new PdfOptions();
        $this->expectException(\Exception::class);
        // @mago-expect analyzer:invalid-property-write
        $opts->tagged = false;
    }

    public function testPdfOptionsWithNullIdentifier(): void
    {
        $opts = new PdfOptions(identifier: 'test');
        $new = $opts->withIdentifier(null);
        static::assertNull($new->identifier);
    }

    public function testPdfOptionsWithNullFirstPage(): void
    {
        $opts = new PdfOptions(first_page: 5, last_page: 10);
        $new = $opts->withFirstPage(null);
        static::assertNull($new->firstPage);
        static::assertSame(10, $new->lastPage);
    }

    public function testPdfOptionsWithNullLastPage(): void
    {
        $opts = new PdfOptions(first_page: 5, last_page: 10);
        $new = $opts->withLastPage(null);
        static::assertNull($new->lastPage);
        static::assertSame(5, $new->firstPage);
    }

    public function testPdfOptionsEqualFirstAndLastPage(): void
    {
        $opts = new PdfOptions(first_page: 3, last_page: 3);
        static::assertSame(3, $opts->firstPage);
        static::assertSame(3, $opts->lastPage);
    }

    public function testPdfOptionsIncompatibleVersionAndValidatorThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incompatible');
        new PdfOptions(version: PdfVersion::V14, validator: PdfValidator::A4);
    }

    public function testPdfOptionsWithVersionIncompatibleThrows(): void
    {
        $opts = new PdfOptions(validator: PdfValidator::A4);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incompatible');
        $opts->withVersion(PdfVersion::V14);
    }

    public function testPdfOptionsWithValidatorIncompatibleThrows(): void
    {
        $opts = new PdfOptions(version: PdfVersion::V14);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incompatible');
        $opts->withValidator(PdfValidator::A4);
    }

    public function testPdfOptionsCompatibleVersionAndValidator(): void
    {
        $opts = new PdfOptions(version: PdfVersion::V17, validator: PdfValidator::A2B);
        static::assertSame(PdfVersion::V17, $opts->version);
        static::assertSame(PdfValidator::A2B, $opts->validator);
    }

    public function testPdfOptionsWithNullVersion(): void
    {
        $opts = new PdfOptions(version: PdfVersion::V17);
        $new = $opts->withVersion(null);
        static::assertNull($new->version);
    }

    public function testPdfOptionsWithNullValidator(): void
    {
        $opts = new PdfOptions(validator: PdfValidator::A2B);
        $new = $opts->withValidator(null);
        static::assertNull($new->validator);
    }
}
