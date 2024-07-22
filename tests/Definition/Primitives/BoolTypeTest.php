<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\BoolType;
use TypescriptSchema\Tests\Definition\TestsParsing;

class BoolTypeTest extends TestCase
{
    use TestsParsing;

    public function testImmutability(): void
    {
        $type = BoolType::make();
        self::assertNotSame($type, $type->coerce());
    }

    public static function parsingDataProvider(): array
    {
        return [
            'bool strict' => [
                BoolType::make(),
                [true, false],
                [1, '1', 'true', '0', -1, 0, 'false', new \stdClass()]
            ],
            'bool coerce' => [
                BoolType::make()->coerce(),
                [true, false, 1, '1', 'true', '0', 0, 'false'],
                [-1, new \stdClass(), []]
            ]
        ];
    }

    public function testToDefinition()
    {
        self::assertEquals('boolean', BoolType::make()->toDefinition()->input);
        self::assertEquals('boolean', BoolType::make()->toDefinition()->output);

        self::assertEquals('boolean', BoolType::make()->coerce()->toDefinition()->output);
        self::assertEquals("boolean|number|null|'true'|'false'", BoolType::make()->coerce()->toDefinition()->input);
    }
}
