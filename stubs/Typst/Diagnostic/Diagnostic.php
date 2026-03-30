<?php

declare(strict_types=1);

namespace Typst\Diagnostic;

use Stringable;
use Typst;

/**
 * A diagnostic message from the Typst compiler.
 *
 * Diagnostics are produced during compilation and represent either errors
 * (which prevent document generation) or warnings (which indicate potential
 * issues but do not block compilation).
 *
 * Obtained from {@see CompilationResult::diagnostics()},
 * {@see CompilationResult::errors()}, or {@see CompilationResult::warnings()}.
 */
final readonly class Diagnostic implements Stringable
{
    /**
     * Returns the severity level of this diagnostic.
     *
     * Use this to distinguish between errors and warnings when
     * processing diagnostics programmatically.
     */
    public function severity(): Severity {}

    /**
     * Returns the diagnostic message text.
     *
     * This is the human-readable description of the error or warning,
     * e.g. "unknown variable: foo" or "font 'MyFont' not found".
     */
    public function message(): string {}

    /**
     * Returns the source location where this diagnostic originated.
     *
     * Returns null for diagnostics that are not tied to a specific
     * source location (e.g. global configuration errors).
     */
    public function span(): ?SourceSpan {}

    /**
     * Returns hint messages that may help resolve the issue.
     *
     * Hints provide actionable suggestions, such as "did you mean 'foo'?"
     * or "try importing the module first". May be empty if no hints
     * are available.
     *
     * @return list<string>
     */
    public function hints(): array {}

    /**
     * Returns a human-readable string representation.
     *
     * Format: "severity: message (at file:line:column)" when a span is
     * available, or "severity: message" when detached.
     */
    public function __toString(): string {}
}
