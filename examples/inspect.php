<?php

declare(strict_types=1);

$world = new Typst\World();
$inspector = new Typst\Inspector($world);

$source = $world->loadString(<<<'TYPST'
    #set page(height: auto)
    #unknown-function()
    TYPST);

$result = $inspector->inspect($source);

if ($result->success()) {
    echo "Compilation succeeded\n";

    exit(0);
}

echo "Compilation failed:\n";
foreach ($result->errors() as $error) {
    echo "  - {$error}\n";
}

foreach ($result->warnings() as $warning) {
    echo "  warning: {$warning}\n";
}

exit(1);
