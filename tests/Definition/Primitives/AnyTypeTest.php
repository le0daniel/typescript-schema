<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\AnyType;
use TypescriptSchema\Tests\Definition\TestsParsing;

class AnyTypeTest extends TestCase
{
    use TestsParsing;

    public function testToDefinition()
    {
        self::assertEquals('any', AnyType::make()->toInputDefinition());
        self::assertEquals('any', AnyType::make()->toOutputDefinition());
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
