<?php

declare(strict_types=1);

$world = new Typst\World(font_dirs: ['/usr/share/fonts', '/usr/local/share/fonts']);
$compiler = new Typst\Compiler($world);

$source = $world->loadString(<<<'TYPST'
    #set page(height: auto)
    #set text(font: "DejaVu Sans")

    = Custom Font Example

    This text uses a system font loaded from a directory.
    TYPST);

$document = $compiler->compile($source);

$document->toPdf()->save(__DIR__ . '/output/custom-fonts.pdf');

echo "Exported custom-fonts.pdf\n";
