<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Definition\Primitives\LiteralType;
use TypescriptSchema\Tests\Mocks\IntBackedEnumMock;
use TypescriptSchema\Tests\Mocks\StringBackedEnumMock;
use TypescriptSchema\Tests\Mocks\UnitEnumMock;

class LiteralTypeTest extends TestCase
{
    #[DataProvider('parsingDataProvider')]
    public function testParsing(bool $success, Type|LiteralType $type, mixed $value, mixed $expectedValue = null): void
    {
        $result = $type->safeParse($value);
        self::assertEquals($success, $result->isSuccess());
        self::assertEquals($success ? ($expectedValue ?? $value) : null, $result->getData());
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

    public function testEnumAsNameString()
    {
        $type = LiteralType::make(UnitEnumMock::SUCCESS);
        self::assertEquals('SUCCESS', $type->enumAsNameString()->parse('SUCCESS'));
        self::assertEquals(UnitEnumMock::SUCCESS, $type->parse('SUCCESS'));
    }

    public function testEnumAsStringChangesReturnTypeCorrectly(): void
    {
        foreach ([UnitEnumMock::SUCCESS, StringBackedEnumMock::ERROR, IntBackedEnumMock::FAILURE] as $enum) {
            $type = LiteralType::make($enum)->enumAsNameString();
            self::assertEquals("'{$enum->name}'", $type->toOutputDefinition());
            self::assertEquals("'{$enum->name}'", $type->toInputDefinition());
        }

        $type = LiteralType::make(123)->enumAsNameString();
        self::assertEquals("123", $type->toOutputDefinition());
    }

    public function testToDefinition(): void
    {
        self::assertEquals("'Test'", LiteralType::make('Test')->toOutputDefinition());
        self::assertEquals('true', LiteralType::make(true)->toOutputDefinition());
        self::assertEquals('false', LiteralType::make(false)->toOutputDefinition());
        self::assertEquals('145', LiteralType::make(145)->toOutputDefinition());

        self::assertEquals("never", LiteralType::make(UnitEnumMock::SUCCESS)->toOutputDefinition());
        self::assertEquals("'SUCCESS'", LiteralType::make(UnitEnumMock::SUCCESS)->toInputDefinition());

        self::assertEquals("0", LiteralType::make(IntBackedEnumMock::SUCCESS)->toOutputDefinition());
        self::assertEquals("'SUCCESS'", LiteralType::make(IntBackedEnumMock::SUCCESS)->toInputDefinition());

        self::assertEquals("'success'", LiteralType::make(StringBackedEnumMock::SUCCESS)->toOutputDefinition());
        self::assertEquals("'SUCCESS'", LiteralType::make(StringBackedEnumMock::SUCCESS)->toInputDefinition());

    }

}
