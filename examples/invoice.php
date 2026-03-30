<?php

declare(strict_types=1);

$data = [
    'number' => 'INV-2026-0042',
    'date' => '2026-02-27',
    'due_date' => '2026-03-29',
    'currency' => 'EUR',
    'tax_rate' => 20.0,
    'from' => [
        'name' => 'Carthage Software Consultancy Limited',
        'address' => "Centre Millenium, Bloc B 2eme etage Bureau N19\nRoute De La Marsa, Sidi Daoud\n2046, Tunisia",
        'email' => 'contact@carthage.software',
        'website' => 'https://carthage.software',
        'business_id' => '0000000V/A/M/000',
    ],
    'to' => [
        'name' => 'Acme Corporation',
        'address' => "1234 Market Street, Suite 500\nSan Francisco, CA 94103\nUnited States",
        'email' => 'accounts@acme.example.com',
    ],
    'items' => [
        [
            'description' => 'Web Application Development',
            'details' => 'Full-stack development of customer portal (160 hours)',
            'quantity' => 160,
            'unit' => 'hour',
            'rate' => 95.00,
        ],
        [
            'description' => 'UI/UX Design',
            'details' => 'Wireframes, mockups, and design system',
            'quantity' => 40,
            'unit' => 'hour',
            'rate' => 110.00,
        ],
        [
            'description' => 'Infrastructure Setup',
            'details' => 'AWS provisioning, CI/CD pipeline, monitoring',
            'quantity' => 24,
            'unit' => 'hour',
            'rate' => 120.00,
        ],
        [
            'description' => 'SSL Certificate (Wildcard)',
            'details' => '1 year, *.acme.example.com',
            'quantity' => 1,
            'unit' => 'unit',
            'rate' => 199.00,
        ],
        [
            'description' => 'Monthly Hosting',
            'details' => 'Production environment, March 2026',
            'quantity' => 1,
            'unit' => 'month',
            'rate' => 450.00,
        ],
    ],
    'payment' => [
        'method' => 'Bank Transfer',
        'bank' => 'Some Bank',
        'iban' => 'TN00 0000 0000 0000 0000 0000',
        'bic' => 'XXYYXXYY',
    ],
    'terms' => 'Payment is due within 30 days of the invoice date. Late payments are subject to a 1.5% monthly interest charge.',
    'notes' => 'Thank you for your continued trust. Please do not hesitate to contact us with any questions regarding this invoice.',
];

$world = new Typst\World(template_dir: __DIR__ . '/templates');
$compiler = new Typst\Compiler($world);

$document = $compiler->compileFile('invoice.typ', ['data' => $data]);

$document->toPdf()->save(__DIR__ . '/output/invoice.pdf');
$document->toImage(options: new Typst\ImageOptions(dpi: 300.0))->save(__DIR__ . '/output/invoice.png');

echo "Exported invoice.pdf and invoice.png\n";
