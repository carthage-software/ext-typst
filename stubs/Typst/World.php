<?php

declare(strict_types=1);

namespace Typst;

/**
 * The Typst compilation environment.
 *
 * Manages font loading, source resolution, caching, and configuration.
 * A world is shared across {@see Compiler} and {@see Inspector} instances
 * and provides the context needed to resolve imports, load packages,
 * and find fonts during compilation.
 *
 * Use `clone` to create an independent copy that shares the base font set
 * but has its own instance-level fonts and configuration. Sources created
 * by a world (or any of its clones) remain compatible with compilers and
 * inspectors created from any world in the same clone lineage.
 */
final class World
{
    /**
     * Creates a new compilation environment.
     *
     * All parameters are optional and default to sensible values for
     * simple use cases. For production use, you will typically want to
     * set at least `template_dir` to control where imports are resolved from.
     *
     * @param string|null $template_dir Root directory for resolving relative file paths and imports.
     *                                  Defaults to the current working directory.
     * @param int<0, max>|null $cache_size Maximum number of cached file/source entries.
     *                                     Defaults to 64. Set to 0 to disable caching entirely.
     *                                     In long-lived processes, tune this based on the number
     *                                     of unique templates/imports your application uses.
     * @param bool|null $embed_default_fonts Whether to embed Typst's default font set
     *                                       (New Computer Modern, DejaVu, and others).
     *                                       Defaults to true. Set to false if you only want
     *                                       to use your own fonts via `$font_dirs` or
     *                                       {@see addFontFile()}/{@see addFontData()}.
     * @param list<non-empty-string>|null $font_dirs Directories to scan recursively for font files
     *                                               (.ttf, .otf, .ttc, .otc). Each directory must
     *                                               exist and be readable.
     * @param string|null $package_dir Local directory for resolving Typst packages.
     *                                 When set, `@preview/package:version` imports are resolved
     *                                 from this directory instead of downloading from the network.
     *
     * @throws Exception\InvalidArgumentException If cache size is negative,
     *                                            or if any font directory does not exist or is not a directory.
     */
    public function __construct(
        ?string $template_dir = null,
        ?int $cache_size = null,
        ?bool $embed_default_fonts = null,
        ?array $font_dirs = null,
        ?string $package_dir = null,
    ) {}

    /**
     * Adds a font from raw binary data (e.g. the contents of a .ttf file).
     *
     * The font becomes available to all compilers and inspectors using
     * this world. Instance-level fonts are not shared with clones.
     *
     * @param string $data Raw font file contents (TrueType, OpenType, or TrueType Collection).
     *
     * @throws Exception\RuntimeException If the font data is invalid or contains no usable fonts.
     */
    public function addFontData(string $data): void {}

    /**
     * Adds a font from a file path.
     *
     * The font file is read immediately and its data is stored in memory.
     * The font becomes available to all compilers and inspectors using this world.
     *
     * @param string $path Path to a font file (.ttf, .otf, .ttc, .otc).
     *
     * @throws Exception\RuntimeException If the file cannot be read or contains no usable fonts.
     */
    public function addFontFile(string $path): void {}

    /**
     * Loads a Typst source from an inline string.
     *
     * The returned {@see Source} object can be compiled multiple times
     * with different inputs. Relative imports in the source are resolved
     * against the world's `template_dir`.
     *
     * @param string $source The Typst markup to load.
     */
    public function loadString(string $source): Source {}

    /**
     * Loads a Typst source from a file.
     *
     * Relative paths are resolved against the world's `template_dir`.
     * The file is read immediately and its contents are stored in the
     * returned {@see Source} object.
     *
     * @param string $path Path to a `.typ` file (absolute or relative to `template_dir`).
     *
     * @throws Exception\RuntimeException If the file cannot be found or read.
     */
    public function loadFile(string $path): Source {}

    /**
     * Returns the font family names available in this world.
     *
     * Includes both shared fonts (embedded defaults and directory scans)
     * and instance-level fonts added via {@see addFontFile()} or
     * {@see addFontData()}. The list is sorted alphabetically and
     * deduplicated.
     *
     * @return list<string>
     */
    public function getFontFamilies(): array {}

    /**
     * Returns debug information about this world's configuration.
     *
     * @return array<string, string>
     */
    public function __debugInfo(): array {}
}
