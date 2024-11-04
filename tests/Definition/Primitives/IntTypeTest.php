<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
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
        self::assertSame(null, IntType::make()->nullable()->parseAndValidate(null, new Context()));
        self::assertSame(123, IntType::make()->nullable()->parseAndValidate(123, new Context()));
        self::assertSame(null, IntType::make()->nullable()->parseAndValidate('abc', new Context(true)));
        self::assertSame(Value::INVALID, IntType::make()->nullable()->parseAndValidate('abc', new Context(false)));
    }

    public function testDefinition(): void
    {
        self::assertSame(['type' => 'integer'], IntType::make()->toDefinition()->toInputSchema());
        self::assertSame(['type' => 'integer'], IntType::make()->toDefinition()->toOutputSchema());

        self::assertSame('number', Typescript::fromJsonSchema(IntType::make()->toDefinition()->toInputSchema()));
        self::assertSame('number', Typescript::fromJsonSchema(IntType::make()->toDefinition()->toOutputSchema()));
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
