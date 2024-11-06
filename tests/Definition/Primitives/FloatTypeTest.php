<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use PHPUnit\Framework\Attributes\DataProvider;
use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Definition\Primitives\NumberType;
use TypescriptSchema\Utils\Typescript;

class FloatTypeTest extends TestCase
{

    #[DataProvider('successfulParsingOfValuesDataProvider')]
    public function testSuccessfulParsingOfValues(mixed $value): void
    {
        self::assertSuccess(NumberType::make()->parseAndValidate($value, new Context()));
    }

    public static function successfulParsingOfValuesDataProvider(): array {
        return [
            'float value' => [145.945],
            'integer value' => [123974],
        ];
    }

    #[DataProvider('successfulWithCoercionDataProvider')]
    public function testSuccessfulWithCoercion(float $expected, mixed $value): void
    {
        self::assertSame($expected, NumberType::make()->coerce()->parseAndValidate($value, new Context()));
    }

    public static function successfulWithCoercionDataProvider(): array {
        return [
            'float value' => [145.945, 145.945],
            'integer value' => [123974, 123974],
            'float as string' => [1.4587, "1.4587"],
            'integer as string' => [19, '19'],
            'boolean true' => [1, true],
            'boolean false' => [0, false],
        ];
    }

    public function testDefinition():void
    {
        self::assertEquals('number', Typescript::fromJsonSchema(NumberType::make()->toDefinition()->input()));
        self::assertEquals('number', Typescript::fromJsonSchema(NumberType::make()->toDefinition()->output()));
    }

}
