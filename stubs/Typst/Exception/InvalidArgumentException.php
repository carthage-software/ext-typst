<?php

declare(strict_types=1);

namespace Typst\Exception;

use Typst;

/**
 * Thrown when a method receives an invalid argument.
 *
 * Common causes include:
 * - Unsupported input value types (objects, resources) in compiler/inspector inputs
 * - Negative page indices, byte offsets, or limits
 * - Out-of-range quality or DPI values in {@see Typst\ImageOptions}
 * - Incompatible PDF version + validator combinations in {@see Typst\PdfOptions}
 * - Invalid page range (first page greater than last page) in {@see Typst\PdfOptions}
 * - Non-existent font directories in {@see Typst\World}
 * - Passing a {@see Typst\Source} to a compiler/inspector from a different world
 */
final class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface {}
