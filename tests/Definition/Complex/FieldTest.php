<?php declare(strict_types=1);

namespace Tests\Definition\Complex;

use TypescriptSchema\Definition\Complex\Field;
use PHPUnit\Framework\TestCase;
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

    public function testDescriptionDocBlock(): void
    {
        self::assertNull(Field::ofType(StringType::make())->getDocBlock());
        self::assertEquals(
            <<<DOC
/**
 * This is a super field
 * With multiple lines
 */
DOC,
            Field::ofType(StringType::make())->describe("This is a super field\nWith multiple lines")->getDocBlock()
        );

        self::assertEquals(
            <<<DOC
/**
 * This is a super field
 * With multiple lines
 * 
 * @deprecated Use this instead. Removal Date: 2022-01-25
 */
DOC,
            Field::ofType(StringType::make())
                ->deprecated('Use this instead', \DateTime::createFromFormat('Y-m-d', '2022-01-25'))
                ->describe("This is a super field\nWith multiple lines")
                ->getDocBlock()
        );

        self::assertEquals(
            <<<DOC
/**
 * @deprecated Use this instead. Removal Date: 2022-01-25
 */
DOC,
            Field::ofType(StringType::make())
                ->deprecated('Use this instead', \DateTime::createFromFormat('Y-m-d', '2022-01-25'))
                ->getDocBlock()
        );

        self::assertEquals(
            <<<DOC
/**
 * @deprecated Removal Date: 2022-01-25
 */
DOC,
            Field::ofType(StringType::make())
                ->deprecated(removalDateTime: \DateTime::createFromFormat('Y-m-d', '2022-01-25'))
                ->getDocBlock()
        );

        self::assertEquals(
            <<<DOC
/**
 * @deprecated
 */
DOC,
            Field::ofType(StringType::make())
                ->deprecated()
                ->getDocBlock()
        );
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
