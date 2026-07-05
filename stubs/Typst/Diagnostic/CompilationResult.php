<?php

declare(strict_types=1);

namespace Typst\Diagnostic;

use Error;
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
    public function getDocument(): ?Typst\Document
    {
        throw new Error(
            'Attempted to call stub method ' . __METHOD__ . '(), which should be implemented by the Typst extension.',
        );
    }

    /**
     * Returns true if compilation succeeded (no errors).
     *
     * A successful compilation may still have warnings. Check
     * {@see hasWarnings()} to determine if any warnings were produced.
     */
    public function success(): bool
    {
        throw new Error(
            'Attempted to call stub method ' . __METHOD__ . '(), which should be implemented by the Typst extension.',
        );
    }

    /**
     * Returns all diagnostics (both errors and warnings).
     *
     * @return list<Diagnostic>
     */
    public function diagnostics(): array
    {
        throw new Error(
            'Attempted to call stub method ' . __METHOD__ . '(), which should be implemented by the Typst extension.',
        );
    }

    /**
     * Returns only warning diagnostics.
     *
     * Warnings indicate potential issues that did not prevent compilation,
     * such as unknown font families or deprecated syntax.
     *
     * @return list<Diagnostic>
     */
    public function warnings(): array
    {
        throw new Error(
            'Attempted to call stub method ' . __METHOD__ . '(), which should be implemented by the Typst extension.',
        );
    }

    /**
     * Returns only error diagnostics.
     *
     * Errors indicate problems that prevented the document from being produced.
     *
     * @return list<Diagnostic>
     */
    public function errors(): array
    {
        throw new Error(
            'Attempted to call stub method ' . __METHOD__ . '(), which should be implemented by the Typst extension.',
        );
    }

    /**
     * Returns true if there are any warning diagnostics.
     */
    public function hasWarnings(): bool
    {
        throw new Error(
            'Attempted to call stub method ' . __METHOD__ . '(), which should be implemented by the Typst extension.',
        );
    }

    /**
     * Returns true if there are any error diagnostics.
     */
    public function hasErrors(): bool
    {
        throw new Error(
            'Attempted to call stub method ' . __METHOD__ . '(), which should be implemented by the Typst extension.',
        );
    }
}
