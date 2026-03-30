<?php

declare(strict_types=1);

namespace Typst;

/**
 * Inspects Typst sources, returning diagnostics instead of throwing.
 *
 * Unlike {@see Compiler} which throws on compilation failure, the inspector
 * returns a {@see Diagnostic\CompilationResult} containing the document
 * (on success) and all diagnostics (errors and warnings).
 *
 * Use the inspector when you need to:
 * - Access compilation warnings (the compiler silently discards them)
 * - Handle errors programmatically without exceptions
 * - Present diagnostic information to end users
 *
 * Each inspector instance maintains its own internal cache, independent
 * of any compiler cache.
 */
final class Inspector
{
    /**
     * Creates a new inspector bound to the given world.
     *
     * The world provides fonts, source resolution, and configuration.
     * Multiple inspectors and compilers can share the same world.
     *
     * @param World $world The compilation environment.
     */
    public function __construct(World $world) {}

    /**
     * Inspects a source and returns a compilation result with diagnostics.
     *
     * The source must have been created by the same {@see World} instance
     * (or a clone of it) that was used to create this inspector.
     *
     * @param Source $source The source to inspect.
     * @param array<string, mixed>|null $inputs Key-value pairs accessible via `sys.inputs` in Typst.
     *                                          Values can be string, int, float, bool, null, or
     *                                          arrays of these types (recursively).
     *
     * @throws Exception\InvalidArgumentException If inputs contain unsupported types (objects, resources),
     *                                            or if the source was created by a different world.
     */
    public function inspect(Source $source, ?array $inputs = null): Diagnostic\CompilationResult {}

    /**
     * Inspects a Typst string and returns a compilation result with diagnostics.
     *
     * Convenience method equivalent to:
     * ```php
     * $inspector->inspect($world->loadString($source), $inputs)
     * ```
     *
     * @param string $source The Typst markup to inspect.
     * @param array<string, mixed>|null $inputs Key-value pairs accessible via `sys.inputs` in Typst.
     *
     * @throws Exception\InvalidArgumentException If inputs contain unsupported types.
     */
    public function inspectString(string $source, ?array $inputs = null): Diagnostic\CompilationResult {}

    /**
     * Inspects a Typst file and returns a compilation result with diagnostics.
     *
     * Relative paths are resolved against the world's template directory.
     * Convenience method equivalent to:
     * ```php
     * $inspector->inspect($world->loadFile($path), $inputs)
     * ```
     *
     * @param string $path Path to a `.typ` file (absolute or relative to template_dir).
     * @param array<string, mixed>|null $inputs Key-value pairs accessible via `sys.inputs` in Typst.
     *
     * @throws Exception\RuntimeException If the file cannot be read.
     * @throws Exception\InvalidArgumentException If inputs contain unsupported types.
     */
    public function inspectFile(string $path, ?array $inputs = null): Diagnostic\CompilationResult {}

    /**
     * Clears all internal caches (file, source, path) and returns the number of cleared entries.
     *
     * Useful in long-lived processes (e.g. RoadRunner, Swoole) to free memory
     * or force re-reading of files that may have changed on disk since they
     * were last cached.
     *
     * @return int<0, max> The total number of cache entries that were cleared.
     */
    public function clearCache(): int {}

    /**
     * Returns a clone of the world used by this inspector.
     *
     * The returned world is an independent copy - modifications to it
     * (e.g. adding fonts) will not affect this inspector.
     */
    public function getWorld(): World {}
}
