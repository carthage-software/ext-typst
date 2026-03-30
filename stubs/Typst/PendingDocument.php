<?php

declare(strict_types=1);

namespace Typst;

/**
 * Represents a document compilation running on a background thread.
 *
 * Created by {@see Compiler::compileInBackground()}. The compilation runs
 * on a separate OS thread. A notification stream (readable PHP resource)
 * becomes readable when compilation finishes, enabling non-blocking
 * integration with any event loop.
 *
 * Typical usage with Revolt:
 *
 * ```php
 * use Revolt\EventLoop;
 *
 * $pending = $compiler->compileInBackground($source);
 * $stream = $pending->getNotificationStream();
 *
 * $suspension = EventLoop::getSuspension();
 * $watcher = EventLoop::onReadable($stream, function (string $id) use ($suspension): void {
 *     EventLoop::cancel($id);
 *     $suspension->resume();
 * });
 *
 * $suspension->suspend();
 * $document = $pending->join();
 * ```
 *
 * This class cannot be cloned. Dropping the pending document before
 * calling {@see join()} will clean up the background thread and pipe.
 */
final class PendingDocument
{
    /**
     * Returns whether the background compilation has finished.
     *
     * This is a non-blocking check. Once this returns `true`, calling
     * {@see join()} will return immediately without blocking.
     */
    public function isReady(): bool {}

    /**
     * Returns a readable PHP stream resource that becomes readable when
     * background compilation finishes.
     *
     * Register this with your event loop (e.g. `EventLoop::onReadable()`)
     * to get notified without polling. The stream receives a single null
     * byte when compilation completes.
     *
     * Can be called multiple times before {@see join()} - each call returns
     * a new PHP stream wrapping the same underlying file descriptor.
     *
     * @return resource A readable PHP stream resource.
     *
     * @throws Exception\LogicException If {@see join()} has already been called.
     */
    public function getNotificationStream() {}

    /**
     * Blocks until the background compilation finishes and returns the document.
     *
     * Closes the notification pipe and consumes the compilation result.
     * After this call, {@see getNotificationStream()} and subsequent
     * {@see join()} calls will throw.
     *
     * @throws Exception\LogicException If this method has already been called.
     * @throws Exception\RuntimeException If compilation failed or the
     *                                    background thread panicked.
     */
    public function join(): Document {}
}
