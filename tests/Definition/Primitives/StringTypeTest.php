<?php declare(strict_types=1);

namespace Definition\Primitives;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Definition\BaseType;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Exceptions\ParsingException;

class StringTypeTest extends TestCase
{

    #[DataProvider('successfulPassesDataProvider')]
    public function testSuccessfulPasses(Type $type, mixed $value, mixed $expected, mixed $failing = null): void
    {
        /** @var BaseType&StringType $type */
        self::assertSame($expected, $type->parse($value));

        if (isset($failing)) {
            $this->expectException(ParsingException::class);
            $type->parse($failing);
        }
    }

    public function testTypescriptDefinition(): void
    {
        self::assertSame('string', StringType::make()->toInputDefinition());
        self::assertSame('string', StringType::make()->toOutputDefinition());
        self::assertSame('string', StringType::make()->lowerCase()->toOutputDefinition());
        self::assertSame('string', StringType::make()->upperCase()->toOutputDefinition());
        self::assertSame('string', StringType::make()->trim()->toOutputDefinition());
    }

    public static function successfulPassesDataProvider(): array {
        return [
            'test simple string' => [
                StringType::make(),
                'My String',
                'My String',
                []
            ],
            'test exact min length' => [
                StringType::make()->min(6),
                'String',
                'String',
                'Strin'
            ],
            'test exact max length' => [
                StringType::make()->max(6),
                'String',
                'String',
                'string+1'
            ],
            'test ends with' => [
                StringType::make()->endsWith('.test'),
                'String.test',
                'String.test',
                'string.not-test'
            ],
            'test email' => [
                StringType::make()->email(),
                'test@me.local',
                'test@me.local',
                'test_e.local',
            ],
            'test email ending in test' => [
                StringType::make()->endsWith('.test')->email(),
                'test@me.test',
                'test@me.test',
                'test_e.test',
            ],
            'test regex' => [
                StringType::make()->regex('/^[a-z]+$/'),
                'mystring',
                'mystring',
                'myString',
            ],
            'test uppercase' => [
                StringType::make()->upperCase(),
                'my-string',
                'MY-STRING',
            ],
            'test lowercase' => [
                StringType::make()->lowerCase(),
                'MY-STRING',
                'my-string',
            ],
            'test trim' => [
                StringType::make()->trim(),
                '   My String  ',
                'My String',
            ],
            'test nullable' => [
                StringType::make()->nullable(),
                null,
                null
            ]
        ];
    }

}
