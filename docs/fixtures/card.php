<?php

$world = new Typst\World(template_dir: __DIR__);
$compiler = new Typst\Compiler($world);

$document = $compiler->compileFile('card.typ', [
    'data' => [
        'name' => 'Jane Smith',
        'title' => 'Senior Developer',
        'email' => 'jane@example.com',
        'website' => 'janesmith.dev',
        'phone' => '+1 (555) 123-4567',
    ],
]);

$document->toPdf()->save('card.pdf');
$document->toImage(options: new Typst\ImageOptions(dpi: 300.0))->save('card.png');
