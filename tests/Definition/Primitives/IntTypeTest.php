<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use PHPUnit\Framework\Attributes\DataProvider;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Primitives\IntType;
use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Utils\Typescript;

class IntTypeTest extends TestCase
{

    public function testSuccessfulParsing(): void
    {
        self::assertSame(0, IntType::make()->parseAndValidate(0, new Context()));
        self::assertSame(1, IntType::make()->parseAndValidate(1, new Context()));
        self::assertSame(123, IntType::make()->parseAndValidate(123, new Context()));
        self::assertSame(123, IntType::make()->coerce()->parseAndValidate('123',  new Context()));

        self::assertSame(null, Executor::execute(IntType::make()->nullable(),null, new Context()));
        self::assertSame(123, Executor::execute(IntType::make()->nullable(),123, new Context()));
        self::assertSame(null, Executor::execute(IntType::make()->nullable(),'abc', new Context(allowPartialFailures: true)));

        self::assertSame(Value::INVALID, Executor::execute(IntType::make()->nullable(),'abc', new Context()));
    }

    public function testDefinition(): void
    {
        self::assertSame(['type' => 'integer'], IntType::make()->toDefinition()->input());
        self::assertSame(['type' => 'integer'], IntType::make()->toDefinition()->output());

        self::assertSame('number', Typescript::fromJsonSchema(IntType::make()->toDefinition()->input()));
        self::assertSame('number', Typescript::fromJsonSchema(IntType::make()->toDefinition()->output()));

        self::assertSame('number|string|boolean', Typescript::fromJsonSchema(IntType::make()->coerce()->toDefinition()->input()));
        self::assertSame('number', Typescript::fromJsonSchema(IntType::make()->coerce()->toDefinition()->output()));
    }

    public function testMinBoundaries()
    {
        self::assertSame(10, IntType::make()->min(10)->parseAndValidate(10, new Context()));
        self::assertSame(Value::INVALID, IntType::make()->min(10)->parseAndValidate(9, new Context()));
    }

    public function testMaxBoundaries()
    {
        self::assertSame(10, IntType::make()->max(10)->parseAndValidate(10, new Context()));
        self::assertSame(Value::INVALID, IntType::make()->max(10)->parseAndValidate(11, new Context()));
    }

    #[DataProvider('failingInputProvider')]
    public function testFailingInput(mixed $value): void
    {
        self::assertSame(Value::INVALID, IntType::make()->parseAndValidate($value, new Context()));
    }

    public static function failingInputProvider(): array
    {
        return [
            'float' => [
                1.23
            ],
            'float as string' => [
                '1.23'
            ],
            'array' => [
                [1,2,3]
            ],
            'object' => [
                (object) ['foo' => 'bar'],
            ],
            'bool' => [
                true,
            ]
        ];
    }

}
