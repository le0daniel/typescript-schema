<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Definition\Complex\Field;
use TypescriptSchema\Definition\Primitives\StringType;

class FieldTest extends TestCase
{
    public function testImmutability()
    {
        $field = Field::ofType(StringType::make());
        self::assertNotSame($field, $field->resolvedBy(fn() => null));
        self::assertNotSame($field, $field->optional());
        self::assertNotSame($field, $field->describe('Something'));
        self::assertNotSame($field, $field->deprecated('Something'));
    }

    public function testDefaultResolver()
    {
        $field = Field::ofType(StringType::make());
        $value = $field->resolveValue('test', ['test' => 456]);
        self::assertEquals(456, $value);
    }

    public function testCustomResolver()
    {
        $field = Field::ofType(StringType::make())->resolvedBy(fn() => 'something');
        self::assertEquals('something', $field->resolveValue('irrelevant', null));
    }

    public function testResolvedByAlias()
    {
        $field = Field::ofType(StringType::make())->alias('other');
        self::assertEquals('other', $field->resolveValue('irrelevant', ['irrelevant' => 'something', 'other' => 'other']));
    }

    public function testIsOptional()
    {
        $field = Field::ofType(StringType::make());
        self::assertTrue($field->optional()->isOptional());
        self::assertFalse($field->isOptional());
    }

    public function testGetType()
    {
        $field = Field::ofType($type = StringType::make());
        self::assertSame($type, $field->getType());
        self::assertSame($type, $field->optional()->getType());
    }
}
