<?php

declare(strict_types=1);

namespace Typst;

/**
 * PDF compliance validators for archival (PDF/A) and accessibility (PDF/UA) standards.
 *
 * PDF/A is a family of ISO standards for long-term archiving of electronic documents.
 * PDF/UA defines requirements for universally accessible PDF documents.
 *
 * Not all combinations of {@see PdfVersion} and {@see PdfValidator} are compatible.
 * For example, PDF/A-1 requires PDF 1.4, while PDF/A-4 requires PDF 2.0.
 * Incompatible combinations throw {@see Exception\InvalidArgumentException}.
 *
 * @link https://en.wikipedia.org/wiki/PDF/A PDF/A overview
 * @link https://en.wikipedia.org/wiki/PDF/UA PDF/UA overview
 *
 * @see PdfOptions::__construct()
 * @see PdfOptions::withValidator()
 */
enum PdfValidator: string
{
    /**
     * PDF/A-1b: basic conformance for long-term archiving.
     *
     * Ensures visual reproducibility of the document. Based on PDF 1.4.
     * The "b" (basic) level guarantees reliable visual reproduction but
     * does not require the document's text to be extractable or searchable.
     *
     * @link https://www.iso.org/standard/38920.html ISO 19005-1:2005
     */
    case A1B = 'a-1b';

    /**
     * PDF/A-1a: accessible conformance for long-term archiving.
     *
     * Extends A-1b with requirements for tagged structure, Unicode character
     * mapping, and logical reading order. Based on PDF 1.4.
     *
     * @link https://www.iso.org/standard/38920.html ISO 19005-1:2005
     */
    case A1A = 'a-1a';

    /**
     * PDF/A-2b: basic conformance, second generation.
     *
     * Extends PDF/A-1 capabilities with JPEG 2000 compression, transparency,
     * layers, and the ability to embed PDF/A-compliant attachments.
     * Based on PDF 1.7 (ISO 32000-1).
     *
     * @link https://www.iso.org/standard/50655.html ISO 19005-2:2011
     */
    case A2B = 'a-2b';

    /**
     * PDF/A-2u: Unicode conformance, second generation.
     *
     * Extends A-2b by requiring all text to have Unicode mappings,
     * enabling reliable text extraction and searching. Based on PDF 1.7.
     *
     * @link https://www.iso.org/standard/50655.html ISO 19005-2:2011
     */
    case A2U = 'a-2u';

    /**
     * PDF/A-2a: accessible conformance, second generation.
     *
     * The highest conformance level of PDF/A-2. Extends A-2u with tagged
     * structure and logical reading order. Based on PDF 1.7.
     *
     * @link https://www.iso.org/standard/50655.html ISO 19005-2:2011
     */
    case A2A = 'a-2a';

    /**
     * PDF/A-3b: basic conformance, third generation.
     *
     * Same capabilities as PDF/A-2b but allows embedding of arbitrary file
     * formats (not just PDF/A-compliant files) as associated files.
     * Based on PDF 1.7.
     *
     * @link https://www.iso.org/standard/57229.html ISO 19005-3:2012
     */
    case A3B = 'a-3b';

    /**
     * PDF/A-3u: Unicode conformance, third generation.
     *
     * Extends A-3b with Unicode text mapping requirements. Allows
     * embedding of arbitrary file formats. Based on PDF 1.7.
     *
     * @link https://www.iso.org/standard/57229.html ISO 19005-3:2012
     */
    case A3U = 'a-3u';

    /**
     * PDF/A-3a: accessible conformance, third generation.
     *
     * The highest conformance level of PDF/A-3. Extends A-3u with tagged
     * structure and logical reading order. Allows embedding of arbitrary
     * file formats. Based on PDF 1.7.
     *
     * @link https://www.iso.org/standard/57229.html ISO 19005-3:2012
     */
    case A3A = 'a-3a';

    /**
     * PDF/A-4: fourth generation archival standard.
     *
     * Based on PDF 2.0 (ISO 32000-2). Simplifies conformance levels:
     * there is no "a", "b", or "u" distinction. All PDF/A-4 files must
     * include Unicode mappings. Tagged structure is optional but recommended.
     *
     * @link https://www.iso.org/standard/71832.html ISO 19005-4:2020
     */
    case A4 = 'a-4';

    /**
     * PDF/A-4f: fourth generation with embedded files.
     *
     * Extends PDF/A-4 by requiring at least one associated file to be embedded.
     * The embedded files may be of any format. Based on PDF 2.0.
     *
     * @link https://www.iso.org/standard/71832.html ISO 19005-4:2020
     */
    case A4F = 'a-4f';

    /**
     * PDF/A-4e: fourth generation for engineering documents.
     *
     * Extends PDF/A-4f with support for 3D models, rich media, and embedded
     * files. Designed for technical and engineering workflows. Based on PDF 2.0.
     *
     * @link https://www.iso.org/standard/71832.html ISO 19005-4:2020
     */
    case A4E = 'a-4e';

    /**
     * PDF/UA-1: universal accessibility, first edition.
     *
     * Defines requirements for accessible PDF documents, including tagged
     * structure, alternative text for images, logical reading order, and
     * navigation aids. Based on PDF 1.7.
     *
     * When using PDF/UA-1, you should also enable tagged PDF output
     * via {@see PdfOptions::withTagged()}.
     *
     * @link https://www.iso.org/standard/64599.html ISO 14289-1:2014
     */
    case Ua1 = 'ua-1';
}
