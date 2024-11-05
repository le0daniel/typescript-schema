<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use PHPUnit\Framework\Attributes\DataProvider;
use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Primitives\LiteralType;
use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Tests\Mocks\IntBackedEnumMock;
use TypescriptSchema\Tests\Mocks\StringBackedEnumMock;
use TypescriptSchema\Tests\Mocks\UnitEnumMock;

class LiteralTypeTest extends TestCase
{
    #[DataProvider('parsingDataProvider')]
    public function testParsing(bool $success, Type $type, mixed $value, mixed $expectedValue = null): void
    {
        $result = $type->parseAndValidate($value, new Context());
        if (!$success) {
            self::assertTrue( $result === Value::INVALID);
            return;
        }

        self::assertTrue( $result !== Value::INVALID);
        if (isset($expectedValue)) {
            self::assertEquals($expectedValue, $result);
        }
    }

    public static function parsingDataProvider(): array
    {
        return [
            'int literal success' => [
                true,
                LiteralType::make(123),
                123,
                123
            ],
            'int literal failure on int as string' => [
                false,
                LiteralType::make(123),
                '123'
            ],
            'string literal success' => [
                true,
                LiteralType::make('my string'),
                'my string'
            ],
            'string literal failure on char change' => [
                false,
                LiteralType::make('my string'),
                'mY string'
            ],
            'bool literal success' => [
                true,
                LiteralType::make(true),
                true,
            ],
            'bool literal failure on bool as string' => [
                false,
                LiteralType::make(true),
                'true',
            ],
            'bool literal failure on bool as int' => [
                false,
                LiteralType::make(true),
                1,
            ],
            'enum as literal success with unit enum' => [
                true,
                LiteralType::make(UnitEnumMock::SUCCESS),
                'SUCCESS',
                UnitEnumMock::SUCCESS
            ],
            'enum as literal success with unit enum as UnitEnum' => [
                true,
                LiteralType::make(UnitEnumMock::SUCCESS),
                UnitEnumMock::SUCCESS,
                UnitEnumMock::SUCCESS,
            ],
            'enum as literal success with String Backed Enum' => [
                true,
                LiteralType::make(StringBackedEnumMock::SUCCESS),
                StringBackedEnumMock::SUCCESS,
                StringBackedEnumMock::SUCCESS,
            ],
            'enum as literal success with string' => [
                true,
                LiteralType::make(StringBackedEnumMock::SUCCESS),
                'SUCCESS',
                StringBackedEnumMock::SUCCESS,
            ],
            'enum as literal failure with string' => [
                false,
                LiteralType::make(StringBackedEnumMock::SUCCESS),
                'success',
            ],
            'enum literal int backed success' => [
                true,
                LiteralType::make(IntBackedEnumMock::SUCCESS),
                IntBackedEnumMock::SUCCESS,
                IntBackedEnumMock::SUCCESS,
            ],
            'enum literal int (value: int) backed success' => [
                true,
                LiteralType::make(IntBackedEnumMock::SUCCESS),
                'SUCCESS',
                IntBackedEnumMock::SUCCESS,
            ],
        ];
    }

    public function testEnumAsStringChangesReturnTypeCorrectly(): void
    {
        foreach ([UnitEnumMock::SUCCESS, StringBackedEnumMock::ERROR, IntBackedEnumMock::FAILURE] as $enum) {
            $type = LiteralType::make($enum);
            self::assertEquals($enum, $type->parseAndValidate($enum->name, new Context()));
            self::assertEquals($enum, $type->parseAndValidate($enum, new Context()));

            self::assertEquals($enum->name, $type->validateAndSerialize($enum->name, new Context()));
            self::assertEquals($enum->name, $type->validateAndSerialize($enum, new Context()));
        }
    }

    public function testToDefinition(): void
    {
        self::assertEquals("Test", LiteralType::make('Test')->toDefinition()->toOutputSchema()['const']);
        self::assertEquals(true, LiteralType::make(true)->toDefinition()->toOutputSchema()['const']);
        self::assertEquals(false, LiteralType::make(false)->toDefinition()->toOutputSchema()['const']);
        self::assertEquals(145, LiteralType::make(145)->toDefinition()->toOutputSchema()['const']);

        self::assertEquals("SUCCESS", LiteralType::make(UnitEnumMock::SUCCESS)->toDefinition()->toOutputSchema()['const']);
        self::assertEquals("SUCCESS", LiteralType::make(IntBackedEnumMock::SUCCESS)->toDefinition()->toInputSchema()['const']);
        self::assertEquals("SUCCESS", LiteralType::make(StringBackedEnumMock::SUCCESS)->toDefinition()->toInputSchema()['const']);
    }

}
