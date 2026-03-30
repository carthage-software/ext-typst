<?php

declare(strict_types=1);

namespace Typst\Output;

use Stringable;
use Typst;

/**
 * A rendered PDF document.
 *
 * Created by {@see Typst\Document::toPdf()}.
 *
 * The PDF data is held in memory and can be accessed via {@see bytes()},
 * saved to disk via {@see save()}, or used as a string (implements {@see Stringable}).
 *
 * @see Typst\PdfOptions for controlling PDF export settings.
 */
final class Pdf implements OutputInterface
{
    /**
     * Returns the raw PDF bytes as a binary string.
     *
     * When called with no arguments, returns the complete PDF data.
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
     * Returns the total byte length of the PDF data.
     */
    public function size(): int {}

    /**
     * Returns the number of pages in the PDF.
     *
     * This reflects the actual page count in the exported PDF, which may
     * differ from {@see Typst\Document::pageCount()} when a page range
     * was specified via {@see Typst\PdfOptions}.
     */
    public function pageCount(): int {}

    /**
     * Writes the PDF to a file on disk.
     *
     * @param string $path Absolute or relative path to the output file.
     *
     * @throws Typst\Exception\RuntimeException If the file cannot be written.
     */
    public function save(string $path): void {}

    /**
     * Returns the raw PDF bytes as a binary string.
     *
     * Equivalent to calling {@see bytes()} with no arguments.
     */
    public function __toString(): string {}
}
