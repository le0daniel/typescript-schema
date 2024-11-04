<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Complex\TupleType;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\IntType;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Utils\Typescript;

class TupleTypeTest extends TestCase
{
    public function testParsing()
    {
        $type = TupleType::make(StringType::make(), IntType::make());
        self::assertSame(['one', 1], $type->parseAndValidate(['one', 1], new Context()));
        self::assertSame(Value::INVALID, $type->parseAndValidate(['one'], new Context()));
        self::assertSame(Value::INVALID, $type->parseAndValidate([1, 'one'], new Context()));
        self::assertSame(Value::INVALID, $type->parseAndValidate(['one' => 'one', 1], new Context()));
        self::assertSame(Value::INVALID, $type->parseAndValidate([], new Context()));
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
        $result = $type->parseAndValidate([123, "9"], $context = new Context());

        self::assertSame(Value::INVALID, $result);
        self::assertCount(2, $context->getIssues());
    }
}
