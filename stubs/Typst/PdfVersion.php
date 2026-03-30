<?php

declare(strict_types=1);

namespace Typst;

/**
 * PDF version to target when exporting a document.
 *
 * Each case corresponds to a specific revision of the PDF specification
 * published by Adobe and later standardized by ISO.
 *
 * Not all versions are compatible with all {@see PdfValidator} values.
 * Incompatible combinations throw {@see Exception\InvalidArgumentException}.
 *
 * @link https://en.wikipedia.org/wiki/PDF#Versions PDF version history
 *
 * @see PdfOptions::__construct()
 * @see PdfOptions::withVersion()
 */
enum PdfVersion: string
{
    /**
     * PDF 1.4, published in 2001.
     *
     * Adds transparency (alpha compositing), JavaScript actions, and
     * embedded file streams. Required by PDF/A-1.
     *
     * @link https://www.iso.org/standard/39938.html ISO 19005-1 (PDF/A-1, based on PDF 1.4)
     */
    case V14 = '1.4';

    /**
     * PDF 1.5, published in 2003.
     *
     * Adds object streams, cross-reference streams, optional content groups
     * (layers), and JPEG 2000 compression.
     */
    case V15 = '1.5';

    /**
     * PDF 1.6, published in 2004.
     *
     * Adds support for OpenType font embedding, AES encryption,
     * and NChannel color spaces.
     */
    case V16 = '1.6';

    /**
     * PDF 1.7, published in 2006, standardized as ISO 32000-1:2008.
     *
     * This is the default version used by Typst when no version is specified.
     * Adds 3D artwork, XFA forms, and package capabilities.
     * Required by PDF/A-2 and PDF/A-3.
     *
     * @link https://www.iso.org/standard/51502.html ISO 32000-1:2008
     */
    case V17 = '1.7';

    /**
     * PDF 2.0, standardized as ISO 32000-2:2020.
     *
     * Major revision with encrypted wrapper documents, page-level output intents,
     * black point compensation, and associated files. Required by PDF/A-4.
     *
     * @link https://www.iso.org/standard/75839.html ISO 32000-2:2020
     */
    case V20 = '2.0';
}
