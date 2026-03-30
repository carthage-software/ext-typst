<?php

declare(strict_types=1);

namespace Typst\Diagnostic;

use Typst;

/**
 * Result of a compilation inspection, containing the document (on success) and diagnostics.
 *
 * Unlike {@see Typst\Compiler} which throws on failure, the {@see Typst\Inspector}
 * returns this result object, giving you access to all diagnostics (errors and warnings)
 * regardless of whether compilation succeeded.
 *
 * @see Typst\Inspector::inspect()
 * @see Typst\Inspector::inspectString()
 * @see Typst\Inspector::inspectFile()
 */
final class CompilationResult
{
    /**
     * Returns the compiled document, or null if compilation failed.
     *
     * This method can be called multiple times and will return a new
     * {@see Typst\Document} instance backed by the same underlying data each time.
     */
    public function getDocument(): ?Typst\Document {}

    /**
     * Returns true if compilation succeeded (no errors).
     *
     * A successful compilation may still have warnings. Check
     * {@see hasWarnings()} to determine if any warnings were produced.
     */
    public function success(): bool {}

    /**
     * Returns all diagnostics (both errors and warnings).
     *
     * @return list<Diagnostic>
     */
    public function diagnostics(): array {}

    /**
     * Returns only warning diagnostics.
     *
     * Warnings indicate potential issues that did not prevent compilation,
     * such as unknown font families or deprecated syntax.
     *
     * @return list<Diagnostic>
     */
    public function warnings(): array {}

    /**
     * Returns only error diagnostics.
     *
     * Errors indicate problems that prevented the document from being produced.
     *
     * @return list<Diagnostic>
     */
    public function errors(): array {}

    /**
     * Returns true if there are any warning diagnostics.
     */
    public function hasWarnings(): bool {}

    /**
     * Returns true if there are any error diagnostics.
     */
    public function hasErrors(): bool {}
}
