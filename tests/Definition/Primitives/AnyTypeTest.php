<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Definition\Primitives\AnyType;
use TypescriptSchema\Tests\Definition\TestsParsing;
use TypescriptSchema\Utils\Typescript;

class AnyTypeTest extends TestCase
{
    use TestsParsing;

    public function testToDefinition()
    {
        self::assertEquals('any', Typescript::fromJsonSchema(AnyType::make()->toDefinition()->toInputSchema()));
        self::assertEquals('any', Typescript::fromJsonSchema(AnyType::make()->toDefinition()->toOutputSchema()));
    }

    public static function parsingDataProvider(): array
    {
        return [
            'null' => [
                AnyType::make(),
                [null, 'string', new \stdClass()]
            ]
        ];
    }
}
