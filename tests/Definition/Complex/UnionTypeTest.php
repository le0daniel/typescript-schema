<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use stdClass;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Complex\ObjectType;
use TypescriptSchema\Definition\Complex\UnionType;
use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Tests\TestCase;
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
        self::assertSame(1, $type->resolveTypeBy(fn() => 1)->resolve(1, new Context()));
    }

    public function testManualResolverWithNamedTypes()
    {
        $type = UnionType::make(string: StringType::make()->coerce(), int: IntType::make());
        self::assertSame(1, $type->resolveTypeBy(fn() => 'int')->resolve(1, new Context()));
        self::assertSame(1, $type->resolveTypeBy(fn() => 1)->resolve(1, new Context()));
    }

    public function testCorrectHandlingWhenUsingSoftFailures(): void
    {
        $type = UnionType::make(StringType::make()->nullable(), IntType::make());

        self::assertSame('string', $type->resolve('string', new Context()));
        self::assertSame(1, $type->resolve(1, new Context()));
        self::assertSame(null, $type->resolve(null, new Context()));
        self::assertSame(Value::INVALID, $type->resolve(new stdClass(), new Context()));

        $complexType = UnionType::make(
            ObjectType::make([
                'id' => IntType::make(),
                'name' => StringType::make()->nullable(),
            ]),
            StringType::make()->nullable(),
        );

        $result = $complexType->resolve(['id' => 5, 'name' => 123], $context = new Context(allowPartialFailures: true));
        self::assertEquals(['id' => 5, 'name' => null], $result);
        self::assertTrue($context->hasIssues());
        self::assertCount(1, $context->getIssues());
        self::assertEquals(['name'], $context->getIssues()[0]->getPath());
    }

    public function testToDefinition()
    {
        $type = UnionType::make(StringType::make(), IntType::make());
        self::assertEquals('string|number', Typescript::fromJsonSchema($type->toDefinition()->input()));
        self::assertEquals('string|number', Typescript::fromJsonSchema($type->toDefinition()->output()));
    }
}
