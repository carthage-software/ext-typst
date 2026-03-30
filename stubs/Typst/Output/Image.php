<?php

declare(strict_types=1);

namespace Typst\Output;

use Typst;

/**
 * A rendered raster image (PNG or JPEG) of a single document page.
 *
 * Created by {@see Typst\Document::toImage()} or as an element of the
 * array returned by {@see Typst\Document::toImages()}.
 *
 * @see Typst\ImageOptions for controlling format, quality, and DPI.
 */
final class Image implements OutputInterface
{
    /**
     * Returns the raw image bytes as a binary string.
     *
     * When called with no arguments, returns the complete image data.
     * Use `$offset` and `$limit` to read a specific chunk for streaming.
     *
     * @param int|null $offset Byte offset to start reading from (default: 0).
     * @param int|null $limit Maximum number of bytes to return (default: all remaining).
     *
     * @throws Typst\Exception\InvalidArgumentException If $offset or $limit is negative.
     * @throws Typst\Exception\OutOfBoundsException If $offset is beyond the data size.
     */
    public function bytes(?int $offset = null, ?int $limit = null): string {}

    /**
     * Returns the total byte length of the image data.
     */
    public function size(): int {}

    /**
     * Returns the image format (PNG or JPEG).
     */
    public function format(): Typst\ImageFormat {}

    /**
     * Returns the image width in pixels.
     *
     * The pixel width depends on the page dimensions and the DPI setting
     * in {@see Typst\ImageOptions}.
     */
    public function width(): int {}

    /**
     * Returns the image height in pixels.
     *
     * The pixel height depends on the page dimensions and the DPI setting
     * in {@see Typst\ImageOptions}.
     */
    public function height(): int {}

    /**
     * Writes the image to a file on disk.
     *
     * @param string $path Absolute or relative path to the output file.
     *
     * @throws Typst\Exception\RuntimeException If the file cannot be written.
     */
    public function save(string $path): void {}

    /**
     * Returns the raw image bytes as a binary string.
     *
     * Equivalent to calling {@see bytes()} with no arguments.
     */
    public function __toString(): string {}
}
