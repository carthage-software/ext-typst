<?php

declare(strict_types=1);

namespace Typst\Exception;

use Typst;

/**
 * Thrown when an index or offset is beyond the valid range.
 *
 * Common causes include:
 * - Page index beyond the document's page count in
 *   {@see Typst\Document::toImage()}, {@see Typst\Document::toSvg()},
 *   {@see Typst\Document::pageWidth()}, or {@see Typst\Document::pageHeight()}
 * - Byte offset beyond the output size in {@see Typst\Output\OutputInterface::bytes()}
 */
final class OutOfBoundsException extends \OutOfBoundsException implements ExceptionInterface {}
