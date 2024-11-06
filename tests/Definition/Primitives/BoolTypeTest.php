<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Definition\Primitives\BoolType;
use TypescriptSchema\Tests\Definition\TestsParsing;
use TypescriptSchema\Utils\Typescript;

class BoolTypeTest extends TestCase
{
    use TestsParsing;

    public function testImmutability(): void
    {
        $type = new BoolType;
        self::assertNotSame($type, $type->coerce());
    }

    public static function parsingDataProvider(): array
    {
        return [
            'bool strict' => [
                (new BoolType),
                [true, false],
                [1, '1', 'true', '0', -1, 0, 'false', new \stdClass()]
            ],
            'bool coerce' => [
                (new BoolType)->coerce(),
                [true, false, 1, '1', 'true', '0', 0, 'false'],
                [-1, new \stdClass(), []]
            ]
        ];
    }

    public function testToDefinition()
    {
        self::assertEquals('boolean', Typescript::fromJsonSchema((new BoolType)->toDefinition()->input()));
        self::assertEquals('boolean', Typescript::fromJsonSchema((new BoolType)->toDefinition()->output()));

        self::assertEquals('boolean', Typescript::fromJsonSchema((new BoolType)->coerce()->toDefinition()->output()));
        //self::assertEquals("boolean|number|null|'true'|'false'", Typescript::fromJsonSchema((new BoolType)->coerce()->toDefinition()->toInputSchema()));
    }
}
