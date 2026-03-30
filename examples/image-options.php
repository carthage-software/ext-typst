<?php

declare(strict_types=1);

$world = new Typst\World();
$compiler = new Typst\Compiler($world);

$source = $world->loadString(<<<'TYPST'
    #set page(height: auto)
    = Image Export Options

    This document demonstrates different image export settings.
    TYPST);

$document = $compiler->compile($source);

$png = (new Typst\ImageOptions())->withDpi(300.0);
$document->toImage(options: $png)->save(__DIR__ . '/output/high-dpi.png');

$jpeg = (new Typst\ImageOptions())
    ->withFormat(Typst\ImageFormat::Jpeg)
    ->withQuality(90);

$document->toImage(options: $jpeg)->save(__DIR__ . '/output/output.jpg');

echo "Exported high-dpi.png, output.jpg\n";
