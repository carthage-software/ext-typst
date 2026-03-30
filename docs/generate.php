<?php

declare(strict_types=1);

namespace Typst\Documentation;

use Psl\File;
use Psl\IO;
use Psl\Json;
use Psl\Str;
use Typst;

require __DIR__ . '/../vendor/autoload.php';

function compile_to_svg(string $template, array $input): string
{
    $world = new Typst\World(template_dir: __DIR__);
    $compiler = new Typst\Compiler($world);
    $source = $world->loadString($template);
    $document = $compiler->compile($source, [
        'data' => $input,
    ]);

    return '<div class="card-output">' . $document->toSvg() . '</div>';
}

$cardPhp = File\read(__DIR__ . '/fixtures/card.php');
$cardTyp = File\read(__DIR__ . '/fixtures/card.typ');
$cardSvg = compile_to_svg($cardTyp, [
    'name' => 'Jane Smith',
    'title' => 'Senior Developer',
    'email' => 'jane@example.com',
    'website' => 'janesmith.dev',
    'phone' => '+1 (555) 123-4567',
]);

$template = File\read(__DIR__ . '/fixtures/template.html');
$markdown = File\read(__DIR__ . '/content.md');
$markdown = Str\replace($markdown, '{{CARD_PHP}}', $cardPhp);
$markdown = Str\replace($markdown, '{{CARD_TYP}}', $cardTyp);
$markdown = Str\replace($markdown, '{{CARD_SVG}}', $cardSvg);
$encoded = Json\encode($markdown);

$html = Str\replace($template, '{{MARKDOWN_CONTENT}}', $encoded);

File\write(__DIR__ . '/index.html', $html);

IO\write_line('Generated documentation at ' . __DIR__ . '/index.html');
