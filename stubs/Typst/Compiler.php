<?php

declare(strict_types=1);

namespace Typst;

/**
 * Compiles Typst sources into documents.
 *
 * The compiler throws {@see Exception\RuntimeException} on compilation failure.
 * Compilation warnings are silently discarded. If you need access to warnings
 * or want non-throwing compilation, use {@see Inspector} instead.
 *
 * Each compiler instance maintains its own internal cache for file reads
 * and source parsing. In long-lived processes, use {@see clearCache()} to
 * free memory or force re-reading of changed files on disk.
 *
 * Compilers are cloneable. Cloning produces an independent compiler that
 * shares the same world but has its own cache.
 */
final class Compiler
{
    /**
     * Creates a new compiler bound to the given world.
     *
     * The world provides fonts, source resolution, and configuration.
     * Multiple compilers can share the same world.
     *
     * @param World $world The compilation environment.
     */
    public function __construct(World $world) {}

    /**
     * Compiles a source into a document.
     *
     * The source must have been created by the same {@see World} instance
     * (or a clone of it) that was used to create this compiler.
     *
     * @param Source $source The source to compile.
     * @param array<string, mixed>|null $inputs Key-value pairs accessible via `sys.inputs` in Typst.
     *                                          Values can be string, int, float, bool, null, or
     *                                          arrays of these types (recursively).
     *
     * @throws Exception\RuntimeException If compilation fails.
     * @throws Exception\InvalidArgumentException If inputs contain unsupported types (objects, resources),
     *                                            or if the source was created by a different world.
     */
    public function compile(Source $source, ?array $inputs = null): Document {}

    /**
     * Compiles a Typst string into a document.
     *
     * Convenience method equivalent to:
     * ```php
     * $compiler->compile($world->loadString($source), $inputs)
     * ```
     *
     * @param string $source The Typst markup to compile.
     * @param array<string, mixed>|null $inputs Key-value pairs accessible via `sys.inputs` in Typst.
     *
     * @throws Exception\RuntimeException If compilation fails.
     * @throws Exception\InvalidArgumentException If inputs contain unsupported types.
     */
    public function compileString(string $source, ?array $inputs = null): Document {}

    /**
     * Compiles a Typst file into a document.
     *
     * Relative paths are resolved against the world's template directory.
     * Convenience method equivalent to:
     * ```php
     * $compiler->compile($world->loadFile($path), $inputs)
     * ```
     *
     * @param string $path Path to a `.typ` file (absolute or relative to template_dir).
     * @param array<string, mixed>|null $inputs Key-value pairs accessible via `sys.inputs` in Typst.
     *
     * @throws Exception\RuntimeException If the file cannot be read or compilation fails.
     * @throws Exception\InvalidArgumentException If inputs contain unsupported types.
     */
    public function compileFile(string $path, ?array $inputs = null): Document {}

    /**
     * Starts a compilation on a background OS thread and returns immediately.
     *
     * The returned {@see PendingDocument} provides a notification stream
     * (readable PHP resource) that becomes readable when compilation finishes.
     * Register it with your event loop to get notified without polling:
     *
     * ```php
     * use Revolt\EventLoop;
     *
     * $pending = $compiler->compileInBackground($source);
     * $stream = $pending->getNotificationStream();
     *
     * $suspension = EventLoop::getSuspension();
     * EventLoop::onReadable($stream, function (string $id) use ($suspension): void {
     *     EventLoop::cancel($id);
     *     $suspension->resume();
     * });
     *
     * $suspension->suspend();
     * $document = $pending->join();
     * ```
     *
     * The source must have been created by the same {@see World} instance
     * that was used to create this compiler. The background thread gets its
     * own compilation world (shared fonts via Arc, fresh caches).
     *
     * @param Source $source The source to compile.
     * @param array<string, mixed>|null $inputs Key-value pairs accessible via `sys.inputs` in Typst.
     *
     * @throws Exception\InvalidArgumentException If inputs contain unsupported types,
     *                                            or the source was created by a different world.
     * @throws Exception\RuntimeException If the notification pipe could not be created.
     */
    public function compileInBackground(Source $source, ?array $inputs = null): PendingDocument {}

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
     * Returns a clone of the world used by this compiler.
     *
     * The returned world is an independent copy - modifications to it
     * (e.g. adding fonts) will not affect this compiler.
     */
    public function getWorld(): World {}

    /**
     * Returns debug information about this compiler's configuration.
     *
     * @return array<string, string>
     */
    public function __debugInfo(): array {}
}
