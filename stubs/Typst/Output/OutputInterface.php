<?php

declare(strict_types=1);

namespace Typst\Output;

use Stringable;
use Typst;

/**
 * Common interface for all output types ({@see Pdf}, {@see Image}, {@see Svg}).
 *
 * Provides methods for reading raw bytes, querying size, saving to disk,
 * and converting to string. All output types are immutable once created.
 *
 * Supports chunked reading via the `$offset` and `$limit` parameters on
 * {@see bytes()}, which is useful for streaming large outputs without
 * copying the entire buffer into PHP memory at once.
 */
interface OutputInterface extends Stringable
{
    /**
     * Returns the raw output bytes as a binary string.
     *
     * When called with no arguments, returns the complete data.
     * Use `$offset` and `$limit` to read a specific chunk without
     * copying the entire buffer, which is useful for streaming large
     * outputs to a file or HTTP response.
     *
     * If `$limit` exceeds the remaining bytes after `$offset`, the
     * result is silently clamped to the available data.
     *
     * @param int|null $offset Byte offset to start reading from (default: 0).
     * @param int|null $limit Maximum number of bytes to return (default: all remaining).
     *
     * @throws Typst\Exception\InvalidArgumentException If $offset or $limit is negative.
     * @throws Typst\Exception\OutOfBoundsException If $offset is beyond the data size.
     */
    public function bytes(?int $offset = null, ?int $limit = null): string;

    /**
     * Returns the total byte length of the output data.
     */
    public function size(): int;

    /**
     * Writes the complete output to a file on disk.
     *
     * Creates the file if it does not exist, or overwrites it if it does.
     * Parent directories must already exist.
     *
     * @param string $path Absolute or relative path to the output file.
     *
     * @throws Typst\Exception\RuntimeException If the file cannot be written
     *                                          (e.g. permission denied, directory does not exist).
     */
    public function save(string $path): void;

    /**
     * Returns the raw output bytes as a binary string.
     *
     * Equivalent to calling {@see bytes()} with no arguments.
     */
    public function __toString(): string;
}
