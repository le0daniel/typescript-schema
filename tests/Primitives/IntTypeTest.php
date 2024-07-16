<?php declare(strict_types=1);

namespace Tests\Primitives;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\IntType;
use TypescriptSchema\Exceptions\ParsingException;

class IntTypeTest extends TestCase
{

    public function testSuccessfulParsing(): void
    {
        self::assertSame(0, IntType::make()->parse(0));
        self::assertSame(1, IntType::make()->parse(1));
        self::assertSame(123, IntType::make()->parse(123));
        self::assertSame(123, IntType::make()->coerce()->parse('123'));
        self::assertSame(null, IntType::make()->nullable()->parse(null));
        self::assertSame(123, IntType::make()->nullable()->parse(123));
    }

    public function testMinBoundaries()
    {
        self::assertSame(10, IntType::make()->min(10)->parse(10));

        $this->expectException(ParsingException::class);
        IntType::make()->min(10)->parse(9);
    }

    public function testMaxBoundaries()
    {
        self::assertSame(10, IntType::make()->max(10)->parse(10));

        $this->expectException(ParsingException::class);
        IntType::make()->max(10)->parse(11);
    }

    #[DataProvider('failingInputProvider')]
    public function testFailingInput(mixed $value): void
    {
        $this->expectException(ParsingException::class);
        IntType::make()->parse($value);
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
