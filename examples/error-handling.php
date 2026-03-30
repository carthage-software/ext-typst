<?php

declare(strict_types=1);

use Typst\Exception\RuntimeException;

$world = new Typst\World();
$compiler = new Typst\Compiler($world);

try {
    $source = $world->loadString('#unknown()');
    $compiler->compile($source);
} catch (RuntimeException $e) {
    echo
        match ($e->getCode()) {
            RuntimeException::COMPILATION_FAILED => "Compilation error: {$e->getMessage()}\n",
            RuntimeException::FILE_NOT_FOUND => "File not found: {$e->getMessage()}\n",
            default => "Error: {$e->getMessage()}\n",
        }
    ;
}
