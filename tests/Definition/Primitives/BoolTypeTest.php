<?php declare(strict_types=1);

namespace Definition\Primitives;

use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\BoolType;

class BoolTypeTest extends TestCase
{

    public function testImmutability(): void
    {
        $type = BoolType::make();
        self::assertNotSame($type, $type->coerce());
    }

    public function testBoolType(): void {
        $type = BoolType::make();

        self::assertTrue($type->parse(true));
        self::assertFalse($type->parse(false));

        $nonStrict = BoolType::make()->coerce();
        self::assertTrue($nonStrict->parse(true));
        self::assertTrue($nonStrict->parse(1));
        self::assertTrue($nonStrict->parse('1'));
        self::assertTrue($nonStrict->parse('true'));

        self::assertFalse($nonStrict->parse(false));
        self::assertFalse($nonStrict->parse(0));
        self::assertFalse($nonStrict->parse('0'));
        self::assertFalse($nonStrict->parse('false'));
    }

}
