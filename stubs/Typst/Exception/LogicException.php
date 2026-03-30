<?php

declare(strict_types=1);

namespace Typst\Exception;

/**
 * Thrown when a method is called in an invalid state or
 * a programming error is detected at runtime.
 *
 * Reserved for future use. Currently no ext-typst methods throw this exception.
 */
final class LogicException extends \LogicException implements ExceptionInterface {}
