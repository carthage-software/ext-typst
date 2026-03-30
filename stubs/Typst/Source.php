<?php

declare(strict_types=1);

namespace Typst;

/**
 * Represents a loaded Typst source.
 *
 * Created by {@see World::loadString()} or {@see World::loadFile()}.
 * A source is bound to the world that created it. Passing it to a
 * {@see Compiler} or {@see Inspector} from a different world throws
 * {@see Exception\InvalidArgumentException}. Cloned worlds share the
 * same lineage, so sources remain compatible across clones.
 *
 * A source can be compiled multiple times with different inputs,
 * making it efficient for template-based workflows where the same
 * markup is rendered with varying data.
 */
final class Source
{
    /**
     * Returns an opaque integer identifier for this source.
     *
     * Useful for logging, tracking, or correlating diagnostics back
     * to specific sources. The identifier is stable for the lifetime
     * of the source object but is not guaranteed to be unique across
     * different worlds.
     *
     * @return int<0, max>
     */
    public function getId(): int {}

    /**
     * Returns the full source text content.
     *
     * For sources loaded from a file, this is the file's content at
     * the time it was loaded. For string sources, this is the string
     * that was passed to {@see World::loadString()}.
     */
    public function getText(): string {}
}
