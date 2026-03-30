<?php

declare(strict_types=1);

namespace Typst\Tests;

use PHPUnit\Framework\TestCase;
use Typst\Compiler;
use Typst\Exception\InvalidArgumentException;
use Typst\Inspector;
use Typst\World;

final class InputTest extends TestCase
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

    public function testStringInput(): void
    {
        $source = self::$world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("key")');
        $doc = self::$compiler->compile($source, ['key' => 'hello']);
        static::assertSame(1, $doc->pageCount());
    }

    public function testIntInput(): void
    {
        $source = self::$world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("num")');
        $doc = self::$compiler->compile($source, ['num' => 42]);
        static::assertSame(1, $doc->pageCount());
    }

    public function testFloatInput(): void
    {
        $source = self::$world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("val")');
        $doc = self::$compiler->compile($source, ['val' => 3.14]);
        static::assertSame(1, $doc->pageCount());
    }

    public function testBoolInput(): void
    {
        $source = self::$world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("flag")');
        $doc = self::$compiler->compile($source, ['flag' => true]);
        static::assertSame(1, $doc->pageCount());
    }

    public function testNullInput(): void
    {
        $source = self::$world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("empty")');
        $doc = self::$compiler->compile($source, ['empty' => null]);
        static::assertSame(1, $doc->pageCount());
    }

    public function testArrayInput(): void
    {
        $source = self::$world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("list")');
        $doc = self::$compiler->compile($source, ['list' => ['a', 'b', 'c']]);
        static::assertSame(1, $doc->pageCount());
    }

    public function testNestedArrayInput(): void
    {
        $source = self::$world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("nested")');
        $doc = self::$compiler->compile($source, ['nested' => ['key' => ['inner' => 'value']]]);
        static::assertSame(1, $doc->pageCount());
    }

    public function testMixedTypeInputs(): void
    {
        $source = self::$world->loadString(
            '#set page(height: auto)' . "\n" . '#sys.inputs.at("str") #sys.inputs.at("num")',
        );
        $doc = self::$compiler->compile($source, [
            'str' => 'hello',
            'num' => 42,
        ]);
        static::assertSame(1, $doc->pageCount());
    }

    public function testObjectInputThrows(): void
    {
        $source = self::$world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("obj")');
        $this->expectException(InvalidArgumentException::class);
        self::$compiler->compile($source, ['obj' => new \stdClass()]);
    }

    public function testResourceInputThrows(): void
    {
        $source = self::$world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("res")');
        $handle = fopen('php://memory', 'r');
        try {
            $this->expectException(InvalidArgumentException::class);
            self::$compiler->compile($source, ['res' => $handle]);
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    public function testNestedObjectInArrayThrows(): void
    {
        $source = self::$world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("data")');
        $this->expectException(InvalidArgumentException::class);
        self::$compiler->compile($source, ['data' => ['nested' => new \stdClass()]]);
    }

    public function testEmptyInputs(): void
    {
        $source = self::$world->loadString("#set page(height: auto)\nHello");
        $doc1 = self::$compiler->compile($source, []);
        $doc2 = self::$compiler->compile($source, null);
        static::assertSame($doc1->pageCount(), $doc2->pageCount());
    }

    public function testInspectWithMixedInputs(): void
    {
        $source = self::$world->loadString('#set page(height: auto)' . "\n" . '#sys.inputs.at("name")');
        $result = self::$inspector->inspect($source, ['name' => 42]);
        static::assertTrue($result->success());
    }
}
