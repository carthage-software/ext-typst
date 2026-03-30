<?php

declare(strict_types=1);

/**
 * Demonstrates chunked reading of output data using bytes($offset, $limit).
 *
 * This is useful for streaming large PDFs/images/SVGs without loading the
 * entire buffer into PHP memory at once (e.g., in RoadRunner or PSR-7 responses).
 */

$world = new Typst\World();
$compiler = new Typst\Compiler($world);

$source = $world->loadString(<<<'TYPST'
    #set page(numbering: "1 / 1")

    = Chunked Reading Demo

    #for i in range(1, 200) {
        [== Section #i]
        [Lorem ipsum dolor sit amet, consectetur adipiscing elit.
        Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
        Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.]
        pagebreak()
    }
    TYPST);

$document = $compiler->compile($source);

$pdf = $document->toPdf();
$totalSize = $pdf->size();

echo "PDF size: {$totalSize} bytes ({$pdf->pageCount()} pages)\n\n";

$chunkSize = 8192;
$outputPath = __DIR__ . '/output/chunked-reading.pdf';
$fp = fopen($outputPath, 'wb');

$offset = 0;
$chunks = 0;
while ($offset < $totalSize) {
    $chunk = $pdf->bytes(offset: $offset, limit: $chunkSize);
    fwrite($fp, $chunk);
    $offset += strlen($chunk);
    $chunks++;
}

fclose($fp);

echo "Wrote {$chunks} chunks to {$outputPath}\n";
echo 'Verified: ' . (file_get_contents($outputPath) === $pdf->bytes() ? 'OK' : 'MISMATCH') . "\n";
