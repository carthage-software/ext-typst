<?php

declare(strict_types=1);

namespace Typst\Output;

use Typst;

/**
 * A rendered SVG (Scalable Vector Graphics) image of a single document page.
 *
 * Created by {@see Typst\Document::toSvg()} or as an element of the
 * array returned by {@see Typst\Document::toSvgs()}.
 *
 * SVG output is resolution-independent and ideal for web embedding
 * or further post-processing with vector graphics tools.
 */
final class Svg implements OutputInterface
{
    /**
     * Returns the SVG markup as a string.
     *
     * When called with no arguments, returns the complete SVG data.
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
     * Returns the total byte length of the SVG data.
     */
    public function size(): int {}

    /**
     * Writes the SVG to a file on disk.
     *
     * @param string $path Absolute or relative path to the output file.
     *
     * @throws Typst\Exception\RuntimeException If the file cannot be written.
     */
    public function save(string $path): void {}

    /**
     * Returns the SVG markup as a string.
     *
     * Equivalent to calling {@see bytes()} with no arguments.
     */
    public function __toString(): string {}
}
