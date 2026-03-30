<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Psl\Async;
use Psl\DateTime;
use Psl\IO;
use Typst\Compiler;
use Typst\Document;
use Typst\Source;
use Typst\World;

function compile_async(Compiler $compiler, Source $source): Document
{
    $pending = $compiler->compileInBackground($source);
    $stream = $pending->getNotificationStream();

    $suspension = Revolt\EventLoop::getSuspension();
    Revolt\EventLoop::onReadable($stream, static function (string $id) use ($suspension): void {
        Revolt\EventLoop::cancel($id);
        $suspension->resume();
    });

    $suspension->suspend();

    return $pending->join();
}

$world = new World(template_dir: __DIR__ . '/templates');
$compiler = new Compiler($world);
$source = $world->loadFile('heavy.typ');

$copies = 10;

IO\write_line('Warming up (1 compile to load fonts/caches)...');
$compiler->compile($source);
$compiler->clearCache();
IO\write_line("Done.\n");

IO\write_line('Compiling %d copies sequentially...', $copies);
$start = DateTime\Timestamp::monotonic();
for ($i = 0; $i < $copies; $i++) {
    $doc = $compiler->compile($source);
}

$duration = DateTime\Timestamp::monotonic()->since($start);
IO\write_line("Sequential: %s  (%d pages each)\n", $duration->toString(), $doc->pageCount());

echo "Compiling {$copies} copies in parallel...";

$start = DateTime\Timestamp::monotonic();
$tasks = [];
for ($i = 0; $i < $copies; $i++) {
    $tasks[] = static fn(): Document => compile_async($compiler, $source);
}

$docs = Async\concurrently($tasks);
$duration = DateTime\Timestamp::monotonic()->since($start);
IO\write_line("Parallel:   %s  (%d pages each)\n", $duration->toString(), $docs[0]->pageCount());

echo "Compiling {$copies} copies with compileInBackground()->join()...";
$start = DateTime\Timestamp::monotonic();

for ($i = 0; $i < $copies; $i++) {
    $doc = $compiler->compileInBackground($source)->join();
}

$duration = DateTime\Timestamp::monotonic()->since($start);
IO\write_line("Bg+Join:    %s  (%d pages each)\n", $duration->toString(), $doc->pageCount());
