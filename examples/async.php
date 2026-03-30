<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Psl\Async;
use Typst\Compiler;
use Typst\Document;
use Typst\Source;
use Typst\World;

/**
 * Compile a source on a background thread, suspending the current fiber
 * until compilation finishes.
 *
 * @param array<string, mixed>|null $inputs
 */
function compile_non_blocking(Compiler $compiler, Source $source, ?array $inputs = null): Document
{
    echo 'started' . PHP_EOL;
    $pending = $compiler->compileInBackground($source, $inputs);
    $stream = $pending->getNotificationStream();

    $suspension = Async\Scheduler::getSuspension();
    Async\Scheduler::onReadable($stream, static function (string $id) use ($suspension): void {
        Async\Scheduler::cancel($id);
        $suspension->resume();
    });

    $suspension->suspend();

    echo 'done' . PHP_EOL;
    return $pending->join();
}

$world = new World(template_dir: __DIR__ . '/templates');
$compiler = new Compiler($world);

$source1 = $world->loadString(<<<'TYPST'
    #set page(width: 200pt, height: 100pt)
    = Hello from Task 1
    This was compiled on a background thread.
    TYPST);

$source2 = $world->loadString(<<<'TYPST'
    #set page(width: 200pt, height: 100pt)
    = Hello from Task 2
    This was also compiled on a background thread.
    TYPST);

[$doc1, $doc2] = Async\concurrently([
    static fn(): Document => compile_non_blocking($compiler, $source1),
    static fn(): Document => compile_non_blocking($compiler, $source2),
]);

$doc1->toPdf()->save(__DIR__ . '/output/task1.pdf');
echo "Saved task1.pdf ({$doc1->pageCount()} page(s))\n";

$doc2->toPdf()->save(__DIR__ . '/output/task2.pdf');
echo "Saved task2.pdf ({$doc2->pageCount()} page(s))\n";

echo "Done - both documents compiled on background threads.\n";
