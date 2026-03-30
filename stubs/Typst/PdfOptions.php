<?php

declare(strict_types=1);

namespace Typst;

/**
 * Configuration options for PDF export.
 *
 * Controls document metadata (identifier, timestamp), page range selection,
 * PDF standard conformance (version and validator), and accessibility tagging.
 *
 * This class is immutable. All `with*` methods return a new instance
 * with the modified value, leaving the original unchanged.
 *
 * @see Document::toPdf()
 */
final readonly class PdfOptions
{
    /**
     * Document identifier used for PDF ID generation.
     *
     * The PDF specification requires two document identifiers in the file
     * trailer. When null, Typst generates them from a hash of the
     * document's title and author metadata.
     *
     * Set this to a stable value (e.g. a database ID or document slug)
     * if you need reproducible PDF output across compilations.
     */
    public ?string $identifier;

    /**
     * Creation timestamp as a Unix timestamp (seconds since epoch, UTC).
     *
     * Embedded in the PDF's document information dictionary as the
     * CreationDate and ModDate fields.
     *
     * When null, the document's `#set document(date: ...)` setting is used.
     */
    public ?int $timestamp;

    /**
     * Zero-based index of the first page to include in the PDF.
     *
     * When null, export starts from the first page (index 0).
     *
     * @var int<0, max>|null
     */
    public ?int $firstPage;

    /**
     * Zero-based index of the last page to include in the PDF (inclusive).
     *
     * When null, export continues through the last page of the document.
     *
     * @var int<0, max>|null
     */
    public ?int $lastPage;

    /**
     * PDF version to target.
     *
     * Determines the feature set available in the output PDF. When null,
     * defaults to PDF 1.7, or is auto-selected based on the validator
     * (e.g. PDF/A-4 requires PDF 2.0).
     *
     * Not all version + validator combinations are compatible.
     *
     * @see PdfVersion for available versions and their capabilities.
     */
    public ?PdfVersion $version;

    /**
     * PDF compliance validator (PDF/A archival or PDF/UA accessibility).
     *
     * When set, the exported PDF will conform to the specified standard.
     * When null, no compliance standard is enforced.
     *
     * Not all version + validator combinations are compatible. For example,
     * PDF/A-1 requires PDF 1.4, while PDF/A-4 requires PDF 2.0.
     *
     * @see PdfValidator for available validators and their ISO standards.
     */
    public ?PdfValidator $validator;

    /**
     * Whether to produce a tagged (structured) PDF for accessibility.
     *
     * Tagged PDFs include a logical structure tree that maps the visual
     * content to semantic elements (headings, paragraphs, tables, etc.),
     * enabling screen readers and assistive technologies to interpret
     * the document.
     *
     * Required by {@see PdfValidator::Ua1} (PDF/UA-1) and recommended
     * for all PDF/A "a"-level conformance (A1A, A2A, A3A).
     *
     * Defaults to true.
     */
    public bool $tagged;

    /**
     * Creates a new PDF options instance.
     *
     * All parameters are optional and default to sensible values.
     *
     * @param string|null $identifier Document identifier for PDF ID generation.
     * @param int|null $timestamp Creation timestamp (Unix seconds, UTC).
     * @param int<0, max>|null $first_page Zero-based first page to include.
     * @param int<0, max>|null $last_page Zero-based last page to include (inclusive).
     * @param PdfVersion|null $version PDF version to target.
     * @param PdfValidator|null $validator PDF compliance validator.
     * @param bool|null $tagged Whether to produce tagged PDF (default: true).
     *
     * @throws Exception\InvalidArgumentException If first or last page is negative,
     *                                            first page is greater than last page,
     *                                            or version and validator are incompatible.
     */
    public function __construct(
        ?string $identifier = null,
        ?int $timestamp = null,
        ?int $first_page = null,
        ?int $last_page = null,
        ?PdfVersion $version = null,
        ?PdfValidator $validator = null,
        ?bool $tagged = null,
    ) {}

    /**
     * Returns a new instance with the given document identifier.
     *
     * @param string|null $identifier The document identifier, or null to use the default (hash of title and author).
     */
    public function withIdentifier(?string $identifier): self {}

    /**
     * Returns a new instance with the given creation timestamp.
     *
     * @param int|null $timestamp Unix timestamp in seconds (UTC), or null to use the document's date setting.
     */
    public function withTimestamp(?int $timestamp): self {}

    /**
     * Returns a new instance with the given first page index.
     *
     * @param int<0, max>|null $first_page Zero-based first page, or null to start from the beginning.
     *
     * @throws Exception\InvalidArgumentException If first page is negative, or greater than the current last page.
     */
    public function withFirstPage(?int $first_page): self {}

    /**
     * Returns a new instance with the given last page index.
     *
     * @param int<0, max>|null $last_page Zero-based last page (inclusive), or null to include through the end.
     *
     * @throws Exception\InvalidArgumentException If last page is negative, or less than the current first page.
     */
    public function withLastPage(?int $last_page): self {}

    /**
     * Returns a new instance with the given PDF version.
     *
     * @param PdfVersion|null $version The target PDF version, or null to use the default.
     *
     * @throws Exception\InvalidArgumentException If the version is incompatible with the current validator.
     */
    public function withVersion(?PdfVersion $version): self {}

    /**
     * Returns a new instance with the given PDF compliance validator.
     *
     * @param PdfValidator|null $validator The compliance standard to enforce, or null for no validation.
     *
     * @throws Exception\InvalidArgumentException If the validator is incompatible with the current version.
     */
    public function withValidator(?PdfValidator $validator): self {}

    /**
     * Returns a new instance with the given tagged PDF setting.
     *
     * @param bool $tagged Whether to produce a tagged (structured) PDF.
     */
    public function withTagged(bool $tagged): self {}
}
