<?php

declare(strict_types=1);

use Typst\ImageOptions;

if (!extension_loaded('typst')) {
    throw new RuntimeException('The typst extension failed to load.');
}

$format = WeakReference::create((new ImageOptions())->format);
if ($format->get() === null) {
    throw new RuntimeException('Returning an enum case freed it.');
}

echo "typst smoke test passed\n";
