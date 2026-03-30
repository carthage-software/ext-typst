<?php

declare(strict_types=1);

namespace Typst\Exception;

use Typst;

/**
 * Thrown for runtime failures during compilation, I/O, font loading, or encoding.
 *
 * Use {@see self::getCode()} to distinguish between error categories:
 *
 * ```php
 * try {
 *     $document = $compiler->compileString($source);
 * } catch (RuntimeException $e) {
 *     match ($e->getCode()) {
 *         RuntimeException::COMPILATION_FAILED => handleCompilationError($e),
 *         RuntimeException::FILE_NOT_FOUND => handleFileNotFound($e),
 *         RuntimeException::WRITE_FAILED => handleWriteError($e),
 *         RuntimeException::FONT_INVALID => handleFontError($e),
 *         RuntimeException::ENCODING_FAILED => handleEncodingError($e),
 *     };
 * }
 * ```
 */
final class RuntimeException extends \RuntimeException implements ExceptionInterface
{
    /**
     * Typst compilation failed.
     *
     * The exception message contains the compiler error details.
     * For structured access to errors and warnings, use
     * {@see Typst\Inspector} instead of {@see Typst\Compiler}.
     */
    public const int COMPILATION_FAILED = 1;

    /**
     * A file could not be found or read.
     *
     * Thrown when {@see Typst\World::loadFile()} or
     * {@see Typst\Compiler::compileFile()} cannot access the specified path.
     */
    public const int FILE_NOT_FOUND = 2;

    /**
     * A file could not be written.
     *
     * Thrown by {@see Typst\Output\OutputInterface::save()} when the
     * output file cannot be created or written to (e.g. permission denied,
     * parent directory does not exist).
     */
    public const int WRITE_FAILED = 3;

    /**
     * Font data is invalid or contains no usable fonts.
     *
     * Thrown by {@see Typst\World::addFontData()} or
     * {@see Typst\World::addFontFile()} when the provided data
     * cannot be parsed as a valid font.
     */
    public const int FONT_INVALID = 4;

    /**
     * Output encoding (PDF, PNG, JPEG, or SVG) failed.
     *
     * Thrown during document export when the rendering pipeline
     * encounters an unrecoverable error.
     */
    public const int ENCODING_FAILED = 5;
}
