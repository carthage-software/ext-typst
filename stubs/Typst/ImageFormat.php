<?php

declare(strict_types=1);

namespace Typst;

/**
 * Supported image output formats for rendering document pages.
 *
 * @see ImageOptions::__construct()
 * @see ImageOptions::withFormat()
 */
enum ImageFormat: string
{
    /**
     * Portable Network Graphics (PNG).
     *
     * Lossless compression, supports transparency. Best for documents
     * with sharp text and vector graphics where quality is paramount.
     *
     * @link https://www.w3.org/TR/png/ W3C PNG Specification
     */
    case Png = 'png';

    /**
     * JPEG (Joint Photographic Experts Group).
     *
     * Lossy compression controlled by the {@see ImageOptions::$quality} setting.
     * Produces smaller files than PNG but may introduce artifacts around sharp
     * edges. Does not support transparency.
     *
     * @link https://www.iso.org/standard/18902.html ISO/IEC 10918-1 (JPEG)
     */
    case Jpeg = 'jpeg';
}
