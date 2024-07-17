<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use TypescriptSchema\Definition\Complex\TupleType;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\IntType;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Tests\Definition\TestsParsing;

class TupleTypeTest extends TestCase
{
    use TestsParsing;

    public static function parsingDataProvider(): array
    {
        return [
            'array' => [
                TupleType::make(StringType::make(), IntType::make()),
                [
                    ['one', 1],
                ],
                [
                    [],
                    ['one'],
                    ['one' => 'one'],
                    [null, null],
                ]
            ]
        ];
    }

    public function testToDefinition(): void
    {
        $type = TupleType::make(StringType::make(), IntType::make());
        self::assertEquals('[string, number]', $type->toDefinition()->input);
        self::assertEquals('[string, number]', $type->toDefinition()->output);

        $type = TupleType::make(StringType::make(), IntType::make()->transform(fn() => true, 'boolean'));
        self::assertEquals('[string, number]', $type->toDefinition()->input);
        self::assertEquals('[string, boolean]', $type->toDefinition()->output);
    }
}
