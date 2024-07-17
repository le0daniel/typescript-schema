<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Utils;

use TypescriptSchema\Tests\Mocks\ArrayAccessMock;
use TypescriptSchema\Tests\Mocks\UnitEnumMock;
use TypescriptSchema\Utils\Serialize;
use PHPUnit\Framework\TestCase;

class SerializeTest extends TestCase
{

    public function testSafeType()
    {
        self::assertEquals('int<1>', Serialize::safeType(1));
        self::assertEquals('float<1.1>', Serialize::safeType(1.1));
        self::assertEquals('string<\'this is a string\'>', Serialize::safeType("this is a string"));
        self::assertEquals('object', Serialize::safeType(new \stdClass()));
        self::assertEquals('array', Serialize::safeType([]));
        self::assertEquals('NULL', Serialize::safeType(null));
        self::assertEquals('closure', Serialize::safeType(fn() => true));
        self::assertEquals('enum<UnitEnumMock::SUCCESS>', Serialize::safeType(UnitEnumMock::SUCCESS));
        self::assertEquals('object<ArrayAccessMock>', Serialize::safeType(new ArrayAccessMock([])));
    }
}
