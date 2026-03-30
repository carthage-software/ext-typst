<?php

declare(strict_types=1);

namespace Typst\Tests;

use PHPUnit\Framework\TestCase;
use Typst\Diagnostic\Severity;
use Typst\ImageFormat;

final class EnumTest extends TestCase
{
    public function testSeverityIsBackedIntEnum(): void
    {
        static::assertTrue(enum_exists(Severity::class));
        $r = new \ReflectionEnum(Severity::class);
        static::assertTrue($r->isBacked());
        static::assertSame('int', (string) $r->getBackingType());
    }

    public function testSeverityCases(): void
    {
        static::assertSame(0, Severity::Error->value);
        static::assertSame(1, Severity::Warning->value);
        static::assertCount(2, Severity::cases());
    }

    public function testSeverityFrom(): void
    {
        static::assertSame(Severity::Error, Severity::from(0));
        static::assertSame(Severity::Warning, Severity::from(1));
    }

    public function testSeverityTryFrom(): void
    {
        static::assertNull(Severity::tryFrom(99));
    }

    public function testSeverityFromInvalidThrows(): void
    {
        $this->expectException(\ValueError::class);
        Severity::from(99);
    }

    public function testImageFormatIsBackedStringEnum(): void
    {
        static::assertTrue(enum_exists(ImageFormat::class));
        $r = new \ReflectionEnum(ImageFormat::class);
        static::assertTrue($r->isBacked());
        static::assertSame('string', (string) $r->getBackingType());
    }

    public function testImageFormatCases(): void
    {
        static::assertSame('png', ImageFormat::Png->value);
        static::assertSame('jpeg', ImageFormat::Jpeg->value);
        static::assertCount(2, ImageFormat::cases());
    }

    public function testImageFormatFrom(): void
    {
        static::assertSame(ImageFormat::Png, ImageFormat::from('png'));
        static::assertSame(ImageFormat::Jpeg, ImageFormat::from('jpeg'));
    }

    public function testImageFormatTryFrom(): void
    {
        static::assertNull(ImageFormat::tryFrom('gif'));
    }

    public function testImageFormatFromInvalidThrows(): void
    {
        $this->expectException(\ValueError::class);
        ImageFormat::from('bmp');
    }
}
