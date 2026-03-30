<?php

declare(strict_types=1);

$world = new Typst\World();
$compiler = new Typst\Compiler($world);

$source = $world->loadString(<<<'TYPST'
    #set page(numbering: "1 / 1")

    = Chapter 1
    Lorem ipsum dolor sit amet.

    #pagebreak()

    = Chapter 2
    Consectetur adipiscing elit.

    #pagebreak()

    = Chapter 3
    Sed do eiusmod tempor incididunt.
    TYPST);

$document = $compiler->compile($source);

echo "Pages: {$document->pageCount()}\n";

$document->toPdf()->save(__DIR__ . '/output/multi-page.pdf');

$images = $document->toImages();
foreach ($images as $i => $image) {
    $page = $i + 1;
    $image->save(__DIR__ . '/output/page-' . $page . '.png');
}

echo 'Exported multi-page.pdf and ' . count($images) . " page images\n";
