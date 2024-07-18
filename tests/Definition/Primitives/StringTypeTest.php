<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Definition\Wrappers\NullableWrapper;

class StringTypeTest extends TestCase
{
    public function testTypescriptDefinition(): void
    {
        self::assertSame('string', StringType::make()->toDefinition()->input);
        self::assertSame('string', StringType::make()->toDefinition()->output);
        self::assertSame('string', StringType::make()->lowerCase()->toDefinition()->output);
        self::assertSame('string', StringType::make()->upperCase()->toDefinition()->output);
        self::assertSame('string', StringType::make()->trim()->toDefinition()->output);
    }

    private function wrap(mixed $value): array
    {
        return is_array($value) ? $value : [$value];
    }

    #[DataProvider('successfulPassesDataProvider')]
    public function testSuccessfulPasses(StringType|NullableWrapper $type, mixed $successful, mixed $failing = null): void
    {
        $successful = $this->wrap($successful ?? []);
        $failing = $this->wrap($failing ?? []);

        foreach ($successful as $value) {
            $result = $type->safeParse($value);
            self::assertTrue($result->isSuccess(), 'Failed for value: ' . $value);
        }

        foreach ($failing as $value) {
            $result = $type->safeParse($value);
            self::assertFalse($result->isSuccess(), "Success for value '{$value}'.");
        }
    }

    private static function stringable(string $value): \Stringable
    {
        return new class ($value) implements \Stringable
        {
            public function __construct(private readonly string $value)
            {
            }

            public function __toString(): string
            {
                return $this->value;
            }
        };
    }

    public static function successfulPassesDataProvider(): array {
        return [
            'simple string' => [
                StringType::make(),
                'My String',
            ],
            'exact min length' => [
                StringType::make()->min(6),
                'String',
                'Strin'
            ],
            'exact max length' => [
                StringType::make()->max(6),
                'String',
                'string+1'
            ],
            'ends with' => [
                StringType::make()->endsWith('.test'),
                'String.test',
                'string.not-test'
            ],
            'start with' => [
                StringType::make()->startsWith('String'),
                ['String.startsWith', 'String'],
                ['other', '']
            ],
            'email' => [
                StringType::make()->email(),
                'test@me.local',
                'test_e.local',
            ],
            'email ending in test' => [
                StringType::make()->endsWith('.test')->email(),
                'test@me.test',
                'test_e.test',
            ],
            'regex' => [
                StringType::make()->regex('/^[a-z]+$/'),
                'mystring',
                'myString',
            ],
            'nullable' => [
                StringType::make()->nullable(),
                [null],
            ],
            'alpha numeric' => [
                StringType::make()->alphaNumeric(),
                ['ThisIsANumericString0123456789', ''],
                ['@', '  a', 'other_']
            ],
            'non empty' => [
                StringType::make()->nonEmpty(),
                '  a',
                [PHP_EOL, '', ' ', '    ', "     \n\r\t\0"]
            ],
            'stringable' => [
                StringType::make(),
                self::stringable('value'),
            ],
        ];
    }

    #[DataProvider('transformDataProvider')]
    public function testTransform(StringType $type, mixed $value, mixed $expected): void
    {
        self::assertEquals($expected, $type->parse($value));
    }

    public static function transformDataProvider(): array
    {
        return [
            'uppercase' => [
                StringType::make()->upperCase(),
                'my-string',
                'MY-STRING',
            ],
            'lowercase' => [
                StringType::make()->lowerCase(),
                'MY-STRING',
                'my-string',
            ],
            'trim' => [
                StringType::make()->trim(),
                '   My String  ',
                'My String',
            ],
        ];
    }

}
