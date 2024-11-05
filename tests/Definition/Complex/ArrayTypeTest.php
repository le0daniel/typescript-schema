<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use TypescriptSchema\Definition\Complex\ArrayType;
use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Definition\Primitives\IntType;
use TypescriptSchema\Helpers\Context;

class ArrayTypeTest extends TestCase
{

    public function testParsingWithArray(): void
    {
        self::assertSame([1,2,5,7], ArrayType::make(IntType::make())->resolve([1,2,5,7], new Context()));
    }

    public function testParsingWithArrayCoercion(): void
    {
        self::assertSame([1,2,5,7], ArrayType::make(IntType::make())->resolve([1,2,5,7], new Context()));
    }

}
