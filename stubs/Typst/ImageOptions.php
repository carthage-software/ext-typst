<?php

declare(strict_types=1);

namespace Typst;

/**
 * Configuration options for image rendering.
 *
 * Controls the output format, compression quality, and resolution when
 * rendering document pages to raster images (PNG or JPEG).
 *
 * This class is immutable. All `with*` methods return a new instance
 * with the modified value, leaving the original unchanged.
 *
 * @see Document::toImage()
 * @see Document::toImages()
 */
final readonly class ImageOptions
{
    /**
     * The output image format.
     *
     * Defaults to {@see ImageFormat::Png}.
     */
    public ImageFormat $format;

    /**
     * JPEG compression quality on a scale of 1 to 100.
     *
     * Higher values produce larger files with fewer compression artifacts.
     * Only affects JPEG output; ignored for PNG.
     *
     * Defaults to 85.
     *
     * @var int<1, 100>
     */
    public int $quality;

    /**
     * Resolution in dots per inch (DPI).
     *
     * Controls the pixel dimensions of the rendered image. Higher DPI
     * produces larger, more detailed images. For reference:
     * - 72 DPI: screen resolution
     * - 144 DPI: 2x retina (default)
     * - 300 DPI: print quality
     *
     * Must be a finite positive number.
     *
     * Defaults to 144.0.
     */
    public float $dpi;

    /**
     * Creates a new image options instance.
     *
     * All parameters are optional and default to sensible values.
     *
     * @param ImageFormat|null $format Output format (default: {@see ImageFormat::Png}).
     * @param int<1, 100>|null $quality JPEG quality, 1-100 (default: 85). Only affects JPEG output.
     * @param float|null $dpi Resolution in dots per inch (default: 144.0). Must be a finite positive number.
     *
     * @throws Exception\InvalidArgumentException If quality is out of the 1-100 range,
     *                                            or DPI is not a finite positive number (zero, negative, NaN, or Inf).
     */
    public function __construct(?ImageFormat $format = null, ?int $quality = null, ?float $dpi = null) {}

    /**
     * Returns a new instance with the given format.
     *
     * @param ImageFormat $format The desired output format.
     */
    public function withFormat(ImageFormat $format): self {}

    /**
     * Returns a new instance with the given JPEG quality.
     *
     * @param int<1, 100> $quality JPEG quality on a scale of 1 (smallest file) to 100 (best quality).
     *
     * @throws Exception\InvalidArgumentException If quality is outside the 1-100 range.
     */
    public function withQuality(int $quality): self {}

    /**
     * Returns a new instance with the given DPI.
     *
     * @param float $dpi Resolution in dots per inch. Must be a finite positive number.
     *
     * @throws Exception\InvalidArgumentException If DPI is not a finite positive number.
     */
    public function withDpi(float $dpi): self {}
}
