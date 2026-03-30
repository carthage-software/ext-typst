<?php

declare(strict_types=1);

$world = new Typst\World();
$compiler = new Typst\Compiler($world);

$source = $world->loadString(<<<'TYPST'
    #set page(height: auto)
    = Hello from Typst

    This is a *bold* statement with _italic_ flair.

    - First item
    - Second item
    - Third item
    TYPST);

$document = $compiler->compile($source);

$document->toPdf()->save(__DIR__ . '/output/hello.pdf');
$document->toImage()->save(__DIR__ . '/output/hello.png');
$document->toSvg()->save(__DIR__ . '/output/hello.svg');

echo "Exported hello.pdf, hello.png, hello.svg\n";
