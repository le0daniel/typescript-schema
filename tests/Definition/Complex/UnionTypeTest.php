<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use stdClass;
use TypescriptSchema\Definition\Complex\ObjectType;
use TypescriptSchema\Definition\Complex\UnionType;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\IntType;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Tests\Definition\TestsParsing;
use TypescriptSchema\Utils\Typescript;

class UnionTypeTest extends TestCase
{
    use TestsParsing;

    public function testImmutability(): void
    {
        $type = UnionType::make();
        self::assertNotSame($type, $type->resolveTypeBy(fn() => null));
    }

    public static function parsingDataProvider(): array
    {
        return [
            'int and string' => [
                UnionType::make(StringType::make(), IntType::make()),
                [
                    1, -1, 'one', '1000'
                ],
                [
                    null, new stdClass(), true, false, 1.1
                ]
            ],
        ];
    }

    public function testManualResolver()
    {
        $type = UnionType::make(StringType::make()->coerce(), IntType::make());
        self::assertSame(1, $type->resolveTypeBy(fn() => 1)->parse(1));
    }

    public function testManualResolverWithNamedTypes()
    {
        $type = UnionType::make(string: StringType::make()->coerce(), int: IntType::make());
        self::assertSame(1, $type->resolveTypeBy(fn() => 'int')->parse(1));
        self::assertSame(1, $type->resolveTypeBy(fn() => 1)->parse(1));
    }

    public function testCorrectHandlingWhenUsingSoftFailures(): void
    {
        $type = UnionType::make(StringType::make()->nullable(), IntType::make());

        self::assertSame('string', $type->safeParse('string', true)->getData());
        self::assertSame(1, $type->safeParse(1, true)->getData());
        self::assertSame(null, $type->safeParse(null, true)->getData());
        self::assertSame(null, $type->safeParse(new stdClass(), true)->getData());

        $complexType = UnionType::make(
            ObjectType::make([
                'id' => IntType::make(),
                'name' => StringType::make()->nullable(),
            ]),
            StringType::make()->nullable(),
        );

        $result = $complexType->safeParse(['id' => 5, 'name' => 123], true);
        self::assertEquals(['id' => 5, 'name' => null], $result->getData());
        self::assertFalse($result->isSuccess());
        self::assertCount(1, $result->issues);
        self::assertEquals(['name'], $result->issues[0]->getPath());
    }

    public function testToDefinition()
    {
        $type = UnionType::make(StringType::make(), IntType::make());
        self::assertEquals('string|number', $type->toDefinition()->input);
        self::assertEquals('string|number', $type->toDefinition()->output);

        $type = UnionType::make(StringType::make(), IntType::make()->transform(fn() => 'some', Typescript::literal('some')));
        self::assertEquals('string|number', $type->toDefinition()->input);
        self::assertEquals('string|\'some\'', $type->toDefinition()->output);
    }
}
