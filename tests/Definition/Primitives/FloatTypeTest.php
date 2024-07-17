<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\FloatType;

class FloatTypeTest extends TestCase
{

    #[DataProvider('successfulParsingOfValuesDataProvider')]
    public function testSuccessfulParsingOfValues(mixed $value): void
    {
        self::assertTrue(FloatType::make()->safeParse($value)->isSuccess());
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
        self::assertSame($expected, FloatType::make()->coerce()->parse($value));
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
        self::assertEquals('number', FloatType::make()->toDefinition()->output);
        self::assertEquals('number', FloatType::make()->toDefinition()->input);
    }

}
