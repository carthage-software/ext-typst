<?php

declare(strict_types=1);

$world = new Typst\World(template_dir: __DIR__ . '/templates');
$compiler = new Typst\Compiler($world);

$document = $compiler->compileFile('playground.typ');

$document->toPdf()->save(__DIR__ . '/output/playground.pdf');

echo "Exported playground.pdf\n";
