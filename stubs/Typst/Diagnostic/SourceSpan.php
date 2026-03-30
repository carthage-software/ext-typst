<?php

declare(strict_types=1);

namespace Typst\Diagnostic;

/**
 * A source location within a Typst file.
 *
 * Identifies the exact position (file, line, column) where a
 * {@see Diagnostic} originated. Useful for error reporting and
 * IDE integration.
 *
 * @see Diagnostic::span()
 */
final readonly class SourceSpan
{
    /**
     * Returns the file path relative to the project root.
     *
     * For inline string sources, this returns a synthetic path
     * like "-" or the internal Typst file identifier.
     */
    public function file(): string {}

    /**
     * Returns the 1-based line number within the source file.
     */
    public function line(): int {}

    /**
     * Returns the 1-based column number within the source line.
     */
    public function column(): int {}

    /**
     * Returns the source text at this span.
     *
     * This is the specific fragment of source code that the
     * diagnostic refers to, not the entire line.
     */
    public function text(): string {}
}
