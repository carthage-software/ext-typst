<?php

declare(strict_types=1);

namespace Typst\Tests;

use PHPUnit\Framework\TestCase;
use Typst\Compiler;
use Typst\Diagnostic\Severity;
use Typst\Document;
use Typst\Exception\RuntimeException;
use Typst\ImageFormat;
use Typst\ImageOptions;
use Typst\Inspector;
use Typst\PdfOptions;
use Typst\PdfVersion;
use Typst\World;

final class TypstTest extends TestCase
{
    private static World $world;
    private static Compiler $compiler;
    private static Inspector $inspector;

    public static function setUpBeforeClass(): void
    {
        self::$world = new World();
        self::$compiler = new Compiler(self::$world);
        self::$inspector = new Inspector(self::$world);
    }

    private function compile(string $body): Document
    {
        $source = self::$world->loadString("#set page(height: auto)\n{$body}");
        return self::$compiler->compile($source);
    }

    private function compileFull(string $source): Document
    {
        return self::$compiler->compile(self::$world->loadString($source));
    }

    public function testBoldText(): void
    {
        $doc = $this->compile('This is *bold* text');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testItalicText(): void
    {
        $doc = $this->compile('This is _italic_ text');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testHeadings(): void
    {
        $doc = $this->compile("= Level 1\n== Level 2\n=== Level 3");
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testBulletList(): void
    {
        $doc = $this->compile("- Apple\n- Banana\n- Cherry");
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testNumberedList(): void
    {
        $doc = $this->compile("+ First\n+ Second\n+ Third");
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testRawCodeBlock(): void
    {
        $doc = $this->compile("```rust\nfn main() {\n    println!(\"hello\");\n}\n```");
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testInlineCode(): void
    {
        $doc = $this->compile('Use `println!` to print');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testStrikethrough(): void
    {
        $doc = $this->compile('#strike[removed]');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testSubscriptAndSuperscript(): void
    {
        $doc = $this->compile('H#sub[2]O and x#super[2]');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testSmallcaps(): void
    {
        $doc = $this->compile('#smallcaps[Small Capitals]');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testUnderline(): void
    {
        $doc = $this->compile('#underline[Underlined text]');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testOverline(): void
    {
        $doc = $this->compile('#overline[Overlined text]');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testHighlight(): void
    {
        $doc = $this->compile('#highlight[Highlighted text]');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testQuote(): void
    {
        $doc = $this->compile('#quote(attribution: [Author])[To be or not to be.]');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testTermList(): void
    {
        $doc = $this->compile("/ Term A: Definition of A\n/ Term B: Definition of B");
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testLink(): void
    {
        $doc = $this->compile('#link("https://example.com")[Click here]');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testFootnote(): void
    {
        $doc = $this->compile('Text with a note#footnote[This is the footnote].');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testCustomPageSize(): void
    {
        $doc = $this->compileFull('#set page(width: 10cm, height: 5cm)' . "\nContent");
        $img = $doc->toImage();
        static::assertGreaterThan($img->height(), $img->width());
    }

    public function testLandscapeVsPortrait(): void
    {
        $portrait = $this->compileFull('#set page(width: 5cm, height: 10cm)' . "\nX");
        $landscape = $this->compileFull('#set page(width: 10cm, height: 5cm)' . "\nX");
        $pImg = $portrait->toImage();
        $lImg = $landscape->toImage();
        static::assertGreaterThan($pImg->width(), $pImg->height());
        static::assertGreaterThan($lImg->height(), $lImg->width());
    }

    public function testHeaderAndFooter(): void
    {
        $doc = $this->compileFull('#set page(header: [My Header], footer: [Page Footer])' . "\nBody content");
        static::assertSame(1, $doc->pageCount());
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testPageNumberInFooter(): void
    {
        $source =
            '#set page(footer: context [Page #counter(page).display()])'
            . "\n"
            . "First page\n#pagebreak()\nSecond page";
        $doc = $this->compileFull($source);
        static::assertSame(2, $doc->pageCount());
    }

    public function testColumnsLayout(): void
    {
        $doc = $this->compile('#columns(2)[Left column. #colbreak() Right column.]');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testPageMargins(): void
    {
        $small = $this->compileFull('#set page(width: 10cm, height: 10cm, margin: 0.5cm)' . "\nX");
        $large = $this->compileFull('#set page(width: 10cm, height: 10cm, margin: 3cm)' . "\nX");
        static::assertSame($small->toImage()->width(), $large->toImage()->width());
    }

    public function testSimpleTable(): void
    {
        $doc = $this->compile('#table(columns: 3, [Name], [Age], [City], [Alice], [30], [NYC], [Bob], [25], [LA])');
        static::assertGreaterThan(0, $doc->toPdf()->size());
    }

    public function testStyledTable(): void
    {
        $doc = $this->compile('#table(columns: 2, fill: (x, y) => if y == 0 { luma(230) }, '
        . '[*Header 1*], [*Header 2*], [Data A], [Data B])');
        static::assertGreaterThan(0, $doc->toPdf()->size());
    }

    public function testTableWithAlignment(): void
    {
        $doc = $this->compile('#table(columns: (1fr, 1fr), align: (left, right), [Left], [Right])');
        static::assertGreaterThan(0, $doc->toPdf()->size());
    }

    public function testTableWithManyRows(): void
    {
        $rows = '';
        for ($i = 1; $i <= 50; $i++) {
            $rows .= "[Row {$i}], [{$i}], ";
        }
        $doc = $this->compile("#table(columns: 2, {$rows})");
        static::assertGreaterThan(0, $doc->toPdf()->size());
    }

    public function testInlineMath(): void
    {
        $doc = $this->compile('The equation $x^2 + y^2 = z^2$ is famous.');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testDisplayMath(): void
    {
        $doc = $this->compile('$ sum_(k=0)^n k = (n(n+1))/2 $');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testMathWithGreekLetters(): void
    {
        $doc = $this->compile('$ alpha + beta = gamma $');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testMathMatrix(): void
    {
        $doc = $this->compile('$ mat(1, 2; 3, 4) $');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testMathFractions(): void
    {
        $doc = $this->compile('$ (a + b) / (c + d) $');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testMathIntegral(): void
    {
        $doc = $this->compile('$ integral_0^infinity e^(-x) dif x = 1 $');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testLetBinding(): void
    {
        $doc = $this->compile('#let name = "World"' . "\nHello, #name!");
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testLetFunction(): void
    {
        $doc = $this->compile('#let double(x) = [#x and #x]' . "\n" . '#double("echo")');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testFunctionWithDefaultArg(): void
    {
        $doc = $this->compile(
            '#let greet(name, greeting: "Hello") = [#greeting, #name!]'
            . "\n"
            . '#greet("Alice")'
            . "\n"
            . '#greet("Bob", greeting: "Hi")',
        );
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testForLoop(): void
    {
        $doc = $this->compile('#for i in range(5) { [Item #i. ] }');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testWhileLoop(): void
    {
        $doc = $this->compile('#let i = 0' . "\n" . '#while i < 3 { [#i ]; i = i + 1 }');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testIfElse(): void
    {
        $doc = $this->compile('#let x = 5' . "\n" . '#if x > 3 [Big] else [Small]');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testArrayAndDictionary(): void
    {
        $doc = $this->compile('#let items = ("A", "B", "C")' . "\n" . '#for item in items { [#item ] }');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testShowRuleOnHeading(): void
    {
        $doc = $this->compile('#show heading: set text(fill: blue)' . "\n" . '= My Heading' . "\n" . 'Body text');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testShowRuleWithFunction(): void
    {
        $doc = $this->compile(
            '#show heading: it => block(fill: luma(230), inset: 8pt, width: 100%, it)' . "\n" . '= Styled Title',
        );
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testShowRuleOnEmph(): void
    {
        $doc = $this->compile('#show emph: set text(fill: red)' . "\n" . 'Normal _emphasized_ normal');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testImportFromFixture(): void
    {
        $world = new World(template_dir: __DIR__ . '/fixtures');
        $c = new Compiler($world);
        $source = $world->loadString(
            '#import "helpers.typ": greet' . "\n" . '#set page(height: auto)' . "\n" . '#greet("Typst")',
        );
        $doc = $c->compile($source);
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testImportMultipleFunctions(): void
    {
        $world = new World(template_dir: __DIR__ . '/fixtures');
        $c = new Compiler($world);
        $source = $world->loadString(
            '#import "helpers.typ": greet, format-date'
            . "\n"
            . '#set page(height: auto)'
            . "\n"
            . '#greet("World")'
            . "\n"
            . '#format-date("2025-01-01")',
        );
        $doc = $c->compile($source);
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testNestedImports(): void
    {
        $world = new World(template_dir: __DIR__ . '/fixtures');
        $c = new Compiler($world);
        $source = $world->loadString(
            '#import "nested-a.typ": compute' . "\n" . '#set page(height: auto)' . "\n" . '#compute(21)',
        );
        $doc = $c->compile($source);
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testImportWildcard(): void
    {
        $world = new World(template_dir: __DIR__ . '/fixtures');
        $c = new Compiler($world);
        $source = $world->loadString(
            '#import "helpers.typ": *' . "\n" . '#set page(height: auto)' . "\n" . '#greet("All")',
        );
        $doc = $c->compile($source);
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testMultipleInputs(): void
    {
        $source = self::$world->loadString(
            '#set page(height: auto)' . "\n" . 'Name: #sys.inputs.at("name"), Role: #sys.inputs.at("role")',
        );
        $doc = self::$compiler->compile($source, ['name' => 'Alice', 'role' => 'Engineer']);
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testInputInConditional(): void
    {
        $source = self::$world->loadString(
            '#set page(height: auto)' . "\n" . '#if sys.inputs.at("lang") == "fr" [Bonjour] else [Hello]',
        );
        $svgFr = self::$compiler->compile($source, ['lang' => 'fr'])->toSvg()->bytes();
        $svgEn = self::$compiler->compile($source, ['lang' => 'en'])->toSvg()->bytes();
        static::assertNotSame($svgFr, $svgEn);
    }

    public function testMissingInputCausesError(): void
    {
        $source = self::$world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("missing")');
        $result = self::$inspector->inspect($source);
        static::assertFalse($result->success());
        static::assertTrue($result->hasErrors());
    }

    public function testInputInFunctionArgument(): void
    {
        $source = self::$world->loadString(
            '#let shout(msg) = upper(msg)' . "\n" . '#set page(height: auto)' . "\n" . '#shout(sys.inputs.at("word"))',
        );
        $doc = self::$compiler->compile($source, ['word' => 'hello']);
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testInputDefaultValue(): void
    {
        $source = self::$world->loadString(
            '#set page(height: auto)' . "\n" . '#sys.inputs.at("key", default: "fallback")',
        );
        $doc = self::$compiler->compile($source);
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testInputKeysIteration(): void
    {
        $source = self::$world->loadString(
            '#set page(height: auto)' . "\n" . '#for (k, v) in sys.inputs { [#k: #v ] }',
        );
        $doc = self::$compiler->compile($source, [
            'a' => '1',
            'b' => '2',
            'c' => '3',
        ]);
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testEmbedPngImage(): void
    {
        $dir = sys_get_temp_dir() . '/typst_img_test_' . uniqid();
        mkdir($dir, 0o777, true);
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==',
            true,
        );
        file_put_contents($dir . '/pixel.png', $png);
        file_put_contents($dir . '/main.typ', '#set page(height: auto)' . "\n" . '#image("pixel.png")');
        try {
            $world = new World(template_dir: $dir);
            $c = new Compiler($world);
            $source = $world->loadFile($dir . '/main.typ');
            $doc = $c->compile($source);
            static::assertSame(1, $doc->pageCount());
            static::assertGreaterThan(0, $doc->toPdf()->size());
        } finally {
            @unlink($dir . '/pixel.png');
            @unlink($dir . '/main.typ');
            @rmdir($dir);
        }
    }

    public function testEmbedSvgImage(): void
    {
        $dir = sys_get_temp_dir() . '/typst_svg_test_' . uniqid();
        mkdir($dir, 0o777, true);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><rect width="10" height="10" fill="red"/></svg>';
        file_put_contents($dir . '/icon.svg', $svg);
        file_put_contents($dir . '/main.typ', '#set page(height: auto)' . "\n" . '#image("icon.svg")');
        try {
            $world = new World(template_dir: $dir);
            $c = new Compiler($world);
            $source = $world->loadFile($dir . '/main.typ');
            $doc = $c->compile($source);
            static::assertSame(1, $doc->pageCount());
            static::assertGreaterThan(0, $doc->toPdf()->size());
        } finally {
            @unlink($dir . '/icon.svg');
            @unlink($dir . '/main.typ');
            @rmdir($dir);
        }
    }

    public function testPdfHeader(): void
    {
        $doc = $this->compile('PDF test');
        static::assertStringStartsWith('%PDF', $doc->toPdf()->bytes());
    }

    public function testPdfPageCountMatchesDocument(): void
    {
        $doc = $this->compileFull("P1\n#pagebreak()\nP2\n#pagebreak()\nP3\n#pagebreak()\nP4");
        static::assertSame(4, $doc->pageCount());
        static::assertSame(4, $doc->toPdf()->pageCount());
    }

    public function testPdfGrowsWithContent(): void
    {
        $small = $this->compile('Short');
        $large = $this->compile(str_repeat("Line of content\n\n", 100));
        static::assertGreaterThan($small->toPdf()->size(), $large->toPdf()->size());
    }

    public function testPdfMultiPageConsistency(): void
    {
        for ($n = 1; $n <= 5; $n++) {
            $pages = implode("\n#pagebreak()\n", array_fill(0, $n, 'Page'));
            $doc = $this->compileFull($pages);
            static::assertSame($n, $doc->toPdf()->pageCount(), "Expected {$n} pages in PDF");
        }
    }

    public function testSvgStartsWithTag(): void
    {
        $doc = $this->compile('SVG test');
        static::assertStringStartsWith('<svg', $doc->toSvg()->bytes());
    }

    public function testSvgDifferentContentDifferentOutput(): void
    {
        $svg1 = $this->compile('Content Alpha')->toSvg()->bytes();
        $svg2 = $this->compile('Content Beta')->toSvg()->bytes();
        static::assertNotSame($svg1, $svg2);
    }

    public function testSvgPerPage(): void
    {
        $doc = $this->compileFull("Page 1\n#pagebreak()\nPage 2");
        $svg0 = $doc->toSvg(0)->bytes();
        $svg1 = $doc->toSvg(1)->bytes();
        static::assertStringStartsWith('<svg', $svg0);
        static::assertStringStartsWith('<svg', $svg1);
        static::assertNotSame($svg0, $svg1);
    }

    public function testPngSignature(): void
    {
        $doc = $this->compile('PNG test');
        $bytes = $doc->toImage()->bytes();
        static::assertSame("\x89PNG\r\n\x1a\n", substr($bytes, 0, 8));
    }

    public function testJpegSignature(): void
    {
        $doc = $this->compile('JPEG test');
        $bytes = $doc->toImage(null, new ImageOptions(ImageFormat::Jpeg))->bytes();
        static::assertSame("\xFF\xD8\xFF", substr($bytes, 0, 3));
    }

    public function testJpegFormat(): void
    {
        $doc = $this->compile('Format test');
        $img = $doc->toImage(null, new ImageOptions(ImageFormat::Jpeg));
        static::assertSame(ImageFormat::Jpeg, $img->format());
    }

    public function testLargerPageLargerImage(): void
    {
        $small = $this->compileFull('#set page(width: 5cm, height: 5cm)' . "\nX");
        $large = $this->compileFull('#set page(width: 20cm, height: 20cm)' . "\nX");
        static::assertGreaterThan($small->toImage()->width(), $large->toImage()->width());
        static::assertGreaterThan($small->toImage()->height(), $large->toImage()->height());
    }

    public function testHigherDpiLargerImage(): void
    {
        $doc = $this->compile('DPI test');
        $low = $doc->toImage(null, new ImageOptions(null, null, 72.0));
        $high = $doc->toImage(null, new ImageOptions(null, null, 300.0));
        static::assertGreaterThan($low->width(), $high->width());
        static::assertGreaterThan($low->height(), $high->height());
    }

    public function testJpegQualityAffectsSize(): void
    {
        $doc = $this->compile(str_repeat('Content with detail. ', 20));
        $low = $doc->toImage(null, new ImageOptions(ImageFormat::Jpeg, 10));
        $high = $doc->toImage(null, new ImageOptions(ImageFormat::Jpeg, 100));
        static::assertGreaterThan($low->size(), $high->size());
    }

    public function testAllPagesRenderedToImages(): void
    {
        $doc = $this->compileFull("A\n#pagebreak()\nB\n#pagebreak()\nC");
        $images = $doc->toImages();
        static::assertCount(3, $images);
        foreach ($images as $img) {
            static::assertGreaterThan(0, $img->width());
            static::assertGreaterThan(0, $img->height());
            static::assertSame("\x89PNG\r\n\x1a\n", substr($img->bytes(), 0, 8));
        }
    }

    public function testInvoiceDocument(): void
    {
        $source = <<<'TYPST'
            #set page(
              paper: "a4",
              header: align(right)[*INVOICE*],
              footer: context align(center)[Page #counter(page).display() of #counter(page).final().first()],
            )
            #set text(size: 11pt)

            *Acme Corp* \
            123 Business St \
            Invoice \#2024-001

            #v(1em)

            #table(
              columns: (1fr, auto, auto, auto),
              [*Item*], [*Qty*], [*Price*], [*Total*],
              [Widget A], [10], [\$5.00], [\$50.00],
              [Widget B], [5], [\$12.00], [\$60.00],
              [Service C], [1], [\$200.00], [\$200.00],
              [], [], [*Subtotal*], [*\$310.00*],
            )
            TYPST;

        $doc = $this->compileFull($source);
        static::assertSame(1, $doc->pageCount());
        $pdf = $doc->toPdf();
        static::assertStringStartsWith('%PDF', $pdf->bytes());
        static::assertSame(1, $pdf->pageCount());
    }

    public function testLetterDocument(): void
    {
        $source = <<<'TYPST'
            #set page(paper: "a4", margin: 2.5cm)
            #set text(size: 12pt)

            #align(right)[
              John Doe \
              456 Elm Street \
              April 15, 2025
            ]

            #v(2em)

            Dear Jane,

            I am writing to confirm our meeting scheduled for next Tuesday.
            Please bring the quarterly reports and the budget proposal.

            Looking forward to seeing you.

            #v(2em)

            Best regards, \
            *John Doe*
            TYPST;

        $doc = $this->compileFull($source);
        static::assertSame(1, $doc->pageCount());
        static::assertGreaterThan(0, $doc->toPdf()->size());
    }

    public function testReportWithOutline(): void
    {
        $source = <<<'TYPST'
            #set page(paper: "a4")
            #set heading(numbering: "1.1")

            #outline()

            = Introduction

            This report covers the analysis.

            == Background

            The project started in January.

            == Methodology

            We used a mixed-methods approach.

            = Results

            The results show significant improvement.

            == Quantitative

            Numbers went up by 25%.

            == Qualitative

            Participants reported satisfaction.

            = Conclusion

            The project was successful.
            TYPST;

        $doc = $this->compileFull($source);
        static::assertGreaterThanOrEqual(1, $doc->pageCount());
        static::assertGreaterThan(0, $doc->toPdf()->size());
    }

    public function testMultiPageReport(): void
    {
        $pages = [];
        for ($i = 1; $i <= 10; $i++) {
            $pages[] = "= Chapter {$i}\n\nContent for chapter {$i}.\n\n" . str_repeat('Filler text. ', 50);
        }
        $source = '#set page(paper: "a4")' . "\n" . implode("\n#pagebreak()\n", $pages);
        $doc = $this->compileFull($source);
        static::assertSame(10, $doc->pageCount());
        static::assertSame(10, $doc->toPdf()->pageCount());
    }

    public function testDocumentWithInputsTemplate(): void
    {
        $source = <<<'TYPST'
            #set page(height: auto)

            #let name = sys.inputs.at("patient_name")
            #let dob = sys.inputs.at("date_of_birth")
            #let id = sys.inputs.at("patient_id")

            = Patient Record

            #table(
              columns: (auto, 1fr),
              [*Name*], [#name],
              [*DOB*], [#dob],
              [*ID*], [#id],
            )
            TYPST;

        $s = self::$world->loadString($source);
        $doc = self::$compiler->compile($s, [
            'patient_name' => 'Jane Smith',
            'date_of_birth' => '1990-05-15',
            'patient_id' => 'P-12345',
        ]);
        static::assertSame(1, $doc->pageCount());
        static::assertGreaterThan(0, $doc->toPdf()->size());
    }

    public function testSetRule(): void
    {
        $doc = $this->compile('#set text(size: 20pt)' . "\nBig text");
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testBlock(): void
    {
        $doc = $this->compile('#block(fill: luma(230), inset: 8pt, radius: 4pt)[Boxed content]');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testGrid(): void
    {
        $doc = $this->compile('#grid(columns: (1fr, 1fr), [Left cell], [Right cell])');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testEnum(): void
    {
        $doc = $this->compile("#enum(\n  [First],\n  [Second],\n  [Third],\n)");
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testFigure(): void
    {
        $doc = $this->compile('#figure(table(columns: 2, [A], [B], [C], [D]), caption: [My Figure])');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testAlignment(): void
    {
        $doc = $this->compile("#align(center)[Centered]\n#align(right)[Right-aligned]");
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testHorizontalLine(): void
    {
        $doc = $this->compile("Above\n#line(length: 100%)\nBelow");
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testBox(): void
    {
        $doc = $this->compile('#box(width: 1cm, height: 1cm, fill: red)');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testCircleAndRect(): void
    {
        $doc = $this->compile('#circle(radius: 5pt, fill: blue) #rect(width: 10pt, height: 10pt, fill: green)');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testStack(): void
    {
        $doc = $this->compile('#stack(dir: ltr, spacing: 1em, [A], [B], [C])');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testPad(): void
    {
        $doc = $this->compile('#pad(x: 2em)[Padded content]');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testRepeat(): void
    {
        $doc = $this->compile('#repeat[.]');
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testInspectValidComplexDocument(): void
    {
        $source = <<<'TYPST'
            #set page(height: auto)
            #set text(size: 11pt)

            = Report Title

            #table(columns: 2, [Key], [Value], [A], [1], [B], [2])

            $ E = m c^2 $

            - Item one
            - Item two
            TYPST;

        $s = self::$world->loadString($source);
        $result = self::$inspector->inspect($s);
        static::assertTrue($result->success());
        static::assertFalse($result->hasErrors());
        $doc = $result->getDocument();
        static::assertNotNull($doc);
        static::assertSame(1, $doc->pageCount());
    }

    public function testInspectWarningOnUnknownFont(): void
    {
        $source = self::$world->loadString(
            '#set page(height: auto)' . "\n" . '#set text(font: "NonExistentFont12345")' . "\n" . 'Text',
        );
        $result = self::$inspector->inspect($source);
        static::assertTrue($result->success());
        static::assertTrue($result->hasWarnings());
        $warnings = $result->warnings();
        static::assertNotEmpty($warnings);
        static::assertSame(Severity::Warning, $warnings[0]->severity());
    }

    public function testInspectErrorHasDetails(): void
    {
        $source = self::$world->loadString('#set page(height: auto)' . "\n" . '#nonexistent()');
        $result = self::$inspector->inspect($source);
        static::assertFalse($result->success());
        $errors = $result->errors();
        static::assertNotEmpty($errors);
        static::assertSame(Severity::Error, $errors[0]->severity());
        static::assertNotEmpty($errors[0]->message());
    }

    public function testInspectMultipleErrors(): void
    {
        $source = self::$world->loadString('#aaa()' . "\n" . '#bbb()' . "\n" . '#ccc()');
        $result = self::$inspector->inspect($source);
        static::assertFalse($result->success());
        static::assertGreaterThanOrEqual(1, count($result->errors()));
    }

    public function testCompilerWorksAfterError(): void
    {
        $source = self::$world->loadString('#invalid-syntax-here()');
        try {
            self::$compiler->compile($source);
        } catch (RuntimeException) {
            // @mago-expect lint:no-empty-catch-clause
        }

        $source2 = self::$world->loadString("#set page(height: auto)\nStill works");
        $doc = self::$compiler->compile($source2);
        static::assertGreaterThan(0, $doc->toSvg()->size());
    }

    public function testConsistentOutputAcrossCompilations(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nConsistency check");
        $svg1 = self::$compiler->compile($source)->toSvg()->bytes();
        $svg2 = self::$compiler->compile($source)->toSvg()->bytes();
        $svg3 = self::$compiler->compile($source)->toSvg()->bytes();
        static::assertSame($svg1, $svg2);
        static::assertSame($svg2, $svg3);
    }

    public function testManySequentialCompilations(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $source = self::$world->loadString("#set page(height: auto)\nIteration {$i}");
            $doc = self::$compiler->compile($source);
            static::assertSame(1, $doc->pageCount());
        }
    }

    public function testPageWidthDefaultPage(): void
    {
        $doc = $this->compileFull('#set page(width: 100pt, height: 50pt)');
        static::assertEqualsWithDelta(100.0, $doc->pageWidth(), 0.01);
    }

    public function testPageHeightDefaultPage(): void
    {
        $doc = $this->compileFull('#set page(width: 100pt, height: 50pt)');
        static::assertEqualsWithDelta(50.0, $doc->pageHeight(), 0.01);
    }

    public function testPageWidthSpecificPage(): void
    {
        $doc = $this->compileFull('#set page(width: 200pt, height: 100pt)
Hello
#pagebreak()
World');
        static::assertEqualsWithDelta(200.0, $doc->pageWidth(page: 1), 0.01);
    }

    public function testPageDimensionsNegativePageThrows(): void
    {
        $doc = $this->compile('Hello');
        $this->expectException(\Typst\Exception\InvalidArgumentException::class);
        $doc->pageWidth(page: -1);
    }

    public function testPageDimensionsOutOfBoundsThrows(): void
    {
        $doc = $this->compile('Hello');
        $this->expectException(\Typst\Exception\OutOfBoundsException::class);
        $doc->pageHeight(page: 999);
    }

    public function testDefaultPageDimensions(): void
    {
        $doc = $this->compileFull('Hello');
        static::assertEqualsWithDelta(595.28, $doc->pageWidth(), 0.5);
        static::assertEqualsWithDelta(841.89, $doc->pageHeight(), 0.5);
    }

    public function testToPdfWithDefaultOptions(): void
    {
        $doc = $this->compile('Hello');
        $pdf = $doc->toPdf(new PdfOptions());
        static::assertGreaterThan(0, $pdf->size());
    }

    public function testToPdfWithIdentifier(): void
    {
        $doc = $this->compile('Hello');
        $pdf = $doc->toPdf(new PdfOptions(identifier: 'test-doc'));
        static::assertGreaterThan(0, $pdf->size());
    }

    public function testToPdfWithTimestamp(): void
    {
        $doc = $this->compile('Hello');
        $pdf = $doc->toPdf(new PdfOptions(timestamp: 1_700_000_000));
        static::assertGreaterThan(0, $pdf->size());
    }

    public function testToPdfWithPageRange(): void
    {
        $doc = $this->compileFull("Page 1\n#pagebreak()\nPage 2\n#pagebreak()\nPage 3");
        static::assertSame(3, $doc->pageCount());

        $fullPdf = $doc->toPdf();
        $partialPdf = $doc->toPdf(new PdfOptions(first_page: 0, last_page: 1, tagged: false));
        static::assertSame(3, $fullPdf->pageCount());
        static::assertSame(2, $partialPdf->pageCount());
    }

    public function testToPdfWithSinglePage(): void
    {
        $doc = $this->compileFull("Page 1\n#pagebreak()\nPage 2\n#pagebreak()\nPage 3");
        $pdf = $doc->toPdf(new PdfOptions(first_page: 1, last_page: 1, tagged: false));
        static::assertSame(1, $pdf->pageCount());
    }

    public function testToPdfWithTaggedFalse(): void
    {
        $doc = $this->compile('Hello');
        $tagged = $doc->toPdf(new PdfOptions(tagged: true));
        $untagged = $doc->toPdf(new PdfOptions(tagged: false));
        static::assertLessThan($tagged->size(), $untagged->size());
    }

    public function testToPdfWithStandard(): void
    {
        $doc = $this->compile('Hello');
        $pdf = $doc->toPdf(new PdfOptions(version: PdfVersion::V17));
        static::assertGreaterThan(0, $pdf->size());
    }

    public function testToPdfWithChainedOptions(): void
    {
        $doc = $this->compileFull("Page 1\n#pagebreak()\nPage 2\n#pagebreak()\nPage 3");
        $opts = (new PdfOptions())
            ->withFirstPage(0)
            ->withLastPage(1)
            ->withTagged(false)
            ->withIdentifier('chained');
        $pdf = $doc->toPdf($opts);
        static::assertSame(2, $pdf->pageCount());
    }
}
