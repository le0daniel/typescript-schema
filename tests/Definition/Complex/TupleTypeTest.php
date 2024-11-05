<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Complex\TupleType;
use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Definition\Primitives\IntType;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Utils\Typescript;

class TupleTypeTest extends TestCase
{
    public function testParsing()
    {
        $type = TupleType::make(StringType::make(), IntType::make());


        self::assertSame(['one', 1], $type->resolve(['one', 1], new Context()));
        self::assertSame(Value::INVALID, $type->resolve(['one'], new Context()));
        self::assertSame(Value::INVALID, $type->resolve([1, 'one'], new Context()));
        self::assertSame(Value::INVALID, $type->resolve(['one' => 'one', 1], new Context()));
        self::assertSame(Value::INVALID, $type->resolve([], new Context()));
    }

    public function testToDefinition(): void
    {
        $type = TupleType::make(StringType::make(), IntType::make());
        self::assertEquals('[string,number]', Typescript::fromJsonSchema($type->toDefinition()->toInputSchema()));
        self::assertEquals('[string,number]', Typescript::fromJsonSchema($type->toDefinition()->toOutputSchema()));
    }

    public function testNumberOfIssuesCollected(): void
    {
        $type = TupleType::make(StringType::make(), IntType::make());
        self::assertIssuesCount(2, $type, [123, "9"]);
    }
}
