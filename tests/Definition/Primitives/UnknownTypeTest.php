<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\UnknownType;

class UnknownTypeTest extends TestCase
{
    public function testToDefinition()
    {
        self::assertEquals('unknown', UnknownType::make()->toInputDefinition());
        self::assertEquals('unknown', UnknownType::make()->toOutputDefinition());
    }

    public function testParse()
    {
        self::assertEquals(null, UnknownType::make()->parse(null));
        self::assertEquals('string', UnknownType::make()->parse('string'));
        self::assertEquals(['string'], UnknownType::make()->parse(['string']));
    }
}
