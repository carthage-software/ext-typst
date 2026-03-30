<?php

declare(strict_types=1);

$world = new Typst\World();
$compiler = new Typst\Compiler($world);

$source = $world->loadString(<<<'TYPST'
    #set page(height: auto)
    = Invoice for #sys.inputs.at("customer")

    Date: #sys.inputs.at("date")

    Thank you for your business.
    TYPST);

$document = $compiler->compile($source, [
    'customer' => 'Acme Corp',
    'date' => date('Y-m-d'),
]);

$document->toPdf()->save(__DIR__ . '/output/invoice.pdf');

echo "Exported invoice.pdf\n";
