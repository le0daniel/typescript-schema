<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use TypescriptSchema\Definition\Complex\ArrayType;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\EnumType;
use TypescriptSchema\Definition\Primitives\IntType;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Tests\Definition\TestsParsing;
use TypescriptSchema\Tests\Mocks\TraversableMock;
use TypescriptSchema\Tests\Mocks\UnitEnumMock;

class ArrayTypeTest extends TestCase
{
    use TestsParsing;

    public static function parsingDataProvider(): array
    {
        return [
            'generator' => [
                ArrayType::make(StringType::make()),
                function () {
                    foreach (['one', 'two', 'three'] as $value) {
                        yield $value;
                    }
                }
            ],
            'array' => [
                ArrayType::make(IntType::make()),
                [
                    [1, 2, 3]
                ],
                [
                    ["a", "2", "3"]
                ]
            ],
            'traversable' => [
                ArrayType::make(StringType::make()),
                new TraversableMock(['one', 'two', 'three']),
            ],
            'fail on non traversable' => [
                ArrayType::make(IntType::make()),
                [],
                [
                    new \stdClass(),
                    null,
                    1,
                    1.2,
                    'string',
                ]
            ]
        ];
    }

    public function testIssuePath(): void
    {
        $type = new ArrayType(StringType::make());
        $result = $type->safeParse([new \stdClass()]);
        self::assertCount(1, $result->issues);
        self::assertEquals([0], $result->issues[0]->getPath());
    }

    public function testDefinitionOfInAndOutput()
    {
        $type = new ArrayType(EnumType::make(UnitEnumMock::class));
        self::assertEquals("Array<'SUCCESS'|'FAILURE'>", $type->toInputDefinition());
        self::assertEquals("Array<never>", $type->toOutputDefinition());
    }
}
