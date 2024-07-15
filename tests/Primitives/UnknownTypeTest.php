<?php declare(strict_types=1);

namespace Tests\Primitives;

use TypescriptSchema\Primitives\UnknownType;
use PHPUnit\Framework\TestCase;

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
