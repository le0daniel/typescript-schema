<?php declare(strict_types=1);

namespace Tests\Utils;

use Tests\Mocks\IntBackedEnumMock;
use Tests\Mocks\StringBackedEnumMock;
use Tests\Mocks\UnitEnumMock;
use TypescriptSchema\Utils\Typescript;
use PHPUnit\Framework\TestCase;

class TypescriptTest extends TestCase
{

    public function testWrapInSingleQuote()
    {
        self::assertEquals("''", Typescript::wrapInSingleQuote(''));
        self::assertEquals("'TEST'", Typescript::wrapInSingleQuote('TEST'));
        self::assertEquals("'TEST String with a quote \''", Typescript::wrapInSingleQuote("TEST String with a quote '"));
    }

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
}
