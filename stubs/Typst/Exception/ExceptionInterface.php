<?php

declare(strict_types=1);

namespace Typst\Exception;

use Throwable;

/**
 * Base interface for all exceptions thrown by the ext-typst extension.
 *
 * Allows catching any Typst-related exception with a single type:
 *
 * ```php
 * use Typst\Exception\ExceptionInterface;
 *
 * try {
 *     $document = $compiler->compileString($source);
 * } catch (ExceptionInterface $e) {
 *     // handles RuntimeException, InvalidArgumentException,
 *     // OutOfBoundsException, and LogicException
 * }
 * ```
 */
interface ExceptionInterface extends Throwable {}
