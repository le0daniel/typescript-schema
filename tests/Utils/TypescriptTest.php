<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Utils;

use TypescriptSchema\Tests\Mocks\IntBackedEnumMock;
use TypescriptSchema\Tests\Mocks\StringBackedEnumMock;
use TypescriptSchema\Tests\Mocks\UnitEnumMock;
use TypescriptSchema\Utils\Typescript;
use PHPUnit\Framework\TestCase;

class TypescriptTest extends TestCase
{
    public function testLiteral()
    {
        self::assertEquals("'string'", Typescript::literal('string'));
        self::assertEquals("123", Typescript::literal(123));
        self::assertEquals("123.03", Typescript::literal(123.03));
        self::assertEquals("true", Typescript::literal(true));
        self::assertEquals("false", Typescript::literal(false));
        self::assertEquals("null", Typescript::literal(null));
    }

    public function testEnumString(): void
    {
        self::assertEquals("'SUCCESS'", Typescript::enumString(IntBackedEnumMock::SUCCESS));
        self::assertEquals("'SUCCESS'", Typescript::enumString(StringBackedEnumMock::SUCCESS));
        self::assertEquals("'SUCCESS'", Typescript::enumString(UnitEnumMock::SUCCESS));
    }

    public function testEnumValueString(): void
    {
        self::assertEquals("'success'", Typescript::enumValueString(StringBackedEnumMock::SUCCESS));
        self::assertEquals("0", Typescript::enumValueString(IntBackedEnumMock::SUCCESS));
    }

    public function testDocWithLines(): void
    {
        self::assertEquals(<<<PLAIN
/**
 * first line
 * second line
 * 
 * after space
 */
PLAIN, Typescript::doc(['first line', 'second line', '', 'after space']));
    }

    public function testEmptyDoc(): void
    {
        self::assertEquals(<<<PLAIN
/**
 * 
 */
PLAIN, Typescript::doc([]));
    }
}
