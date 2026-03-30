<?php

declare(strict_types=1);

namespace Typst;

/**
 * A compiled Typst document that can be exported to PDF, images, or SVG.
 *
 * Created by {@see Compiler::compile()} or obtained from
 * {@see Diagnostic\CompilationResult::getDocument()}.
 *
 * A document is immutable and holds the fully laid-out pages in memory.
 * Export methods can be called multiple times with different options
 * without recompilation.
 *
 * Documents are not cloneable. Create a new one by compiling again
 * if you need a separate instance.
 */
final class Document
{
    /**
     * Returns the total number of pages in the document.
     */
    public function pageCount(): int {}

    /**
     * Returns the width of a page in typographic points (1 pt = 1/72 inch).
     *
     * Different pages may have different dimensions if the Typst source
     * changes page settings mid-document.
     *
     * @param int|null $page Zero-based page index (default: 0, the first page).
     *
     * @throws Exception\InvalidArgumentException If the page index is negative.
     * @throws Exception\OutOfBoundsException If the page index is beyond the document's page count.
     */
    public function pageWidth(?int $page = null): float {}

    /**
     * Returns the height of a page in typographic points (1 pt = 1/72 inch).
     *
     * Different pages may have different dimensions if the Typst source
     * changes page settings mid-document.
     *
     * @param int|null $page Zero-based page index (default: 0, the first page).
     *
     * @throws Exception\InvalidArgumentException If the page index is negative.
     * @throws Exception\OutOfBoundsException If the page index is beyond the document's page count.
     */
    public function pageHeight(?int $page = null): float {}

    /**
     * Exports the document as a PDF.
     *
     * When called without options, produces a tagged PDF 1.7 document
     * containing all pages. Use {@see PdfOptions} to control the version,
     * compliance standard, page range, and other settings.
     *
     * @param PdfOptions|null $options PDF export options, or null for defaults.
     *
     * @throws Exception\InvalidArgumentException If the PDF options contain an incompatible
     *                                            version + validator combination.
     * @throws Exception\RuntimeException If PDF export fails.
     *
     * @see PdfOptions for available export settings.
     * @see PdfVersion for PDF version selection.
     * @see PdfValidator for PDF/A and PDF/UA compliance.
     */
    public function toPdf(?PdfOptions $options = null): Output\Pdf {}

    /**
     * Renders a single page as a raster image (PNG or JPEG).
     *
     * @param int|null $page Zero-based page index (default: 0, the first page).
     * @param ImageOptions|null $options Image rendering options, or null for defaults
     *                                   (PNG, quality 85, 144 DPI).
     *
     * @throws Exception\InvalidArgumentException If the page index is negative.
     * @throws Exception\OutOfBoundsException If the page index is beyond the document's page count.
     * @throws Exception\RuntimeException If rendering fails.
     *
     * @see ImageOptions for controlling format, quality, and DPI.
     */
    public function toImage(?int $page = null, ?ImageOptions $options = null): Output\Image {}

    /**
     * Renders all pages as raster images (PNG or JPEG).
     *
     * Returns one {@see Output\Image} per page in document order.
     *
     * @param ImageOptions|null $options Image rendering options applied to all pages,
     *                                   or null for defaults (PNG, quality 85, 144 DPI).
     *
     * @return list<Output\Image>
     *
     * @throws Exception\RuntimeException If rendering fails.
     *
     * @see ImageOptions for controlling format, quality, and DPI.
     */
    public function toImages(?ImageOptions $options = null): array {}

    /**
     * Exports a single page as SVG (Scalable Vector Graphics).
     *
     * SVG output is resolution-independent and ideal for web embedding
     * or further post-processing with vector graphics tools.
     *
     * @param int|null $page Zero-based page index (default: 0, the first page).
     *
     * @throws Exception\InvalidArgumentException If the page index is negative.
     * @throws Exception\OutOfBoundsException If the page index is beyond the document's page count.
     * @throws Exception\RuntimeException If SVG export fails.
     */
    public function toSvg(?int $page = null): Output\Svg {}

    /**
     * Exports all pages as SVG (Scalable Vector Graphics).
     *
     * Returns one {@see Output\Svg} per page in document order.
     *
     * @return list<Output\Svg>
     */
    public function toSvgs(): array {}
}
