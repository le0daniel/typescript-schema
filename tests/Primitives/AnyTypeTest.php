<?php declare(strict_types=1);

namespace Tests\Primitives;

use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\AnyType;

class AnyTypeTest extends TestCase
{

    public function testToDefinition()
    {
        self::assertEquals('any', AnyType::make()->toInputDefinition());
        self::assertEquals('any', AnyType::make()->toOutputDefinition());
    }

    public function testParse()
    {
        self::assertEquals(null, AnyType::make()->parse(null));
        self::assertEquals('string', AnyType::make()->parse('string'));
        self::assertEquals(['string'], AnyType::make()->parse(['string']));
    }
}
