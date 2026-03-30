<?php

declare(strict_types=1);

namespace Typst\Diagnostic;

/**
 * Severity level of a diagnostic message from the Typst compiler.
 *
 * @see Diagnostic::severity()
 */
enum Severity: int
{
    /**
     * A compilation error that prevents the document from being produced.
     *
     * When at least one error diagnostic is present, the compilation result
     * will have no document and {@see CompilationResult::success()} returns false.
     */
    case Error = 0;

    /**
     * A non-fatal warning that does not prevent compilation.
     *
     * Common examples include unknown font families (which fall back to
     * a default) and deprecated syntax. The document is still produced
     * but may not render as intended.
     */
    case Warning = 1;
}
