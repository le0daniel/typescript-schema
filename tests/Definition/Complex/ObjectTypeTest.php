<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Complex\Field;
use TypescriptSchema\Definition\Complex\ObjectType;
use TypescriptSchema\Definition\Primitives\IntType;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Tests\Mocks\GettersMock;
use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Utils\Typescript;

class ObjectTypeTest extends TestCase
{

    public function testImmutability(): void
    {
        $object = ObjectType::make(['name' => StringType::make()]);
        self::assertNotSame($object, $object->passThrough());
    }

    public function testObjectTypeDefinition()
    {
        $type = ObjectType::make([
            'id' => StringType::make(),
            'name' => StringType::make()->nullable(),
            'opt' => Field::ofType(StringType::make()->nullable())->optional(),
        ]);

        self::assertSame('{id:string;name:string|null;opt?:string|null}', Typescript::fromJsonSchema($type->toDefinition()->output()));
        self::assertTrue($type->toDefinition()->input() === $type->toDefinition()->output());
        self::assertSame('{id:string;name:string|null;opt?:string|null;[key: string]:any}', Typescript::fromJsonSchema($type->passThrough()->toDefinition()->output()));
        self::assertTrue($type->passThrough()->toDefinition()->input() === $type->passThrough()->toDefinition()->output());
    }

    public function testPassThroughAsClosure(): void
    {
        $type = ObjectType::make([
            'id' => StringType::make(),
        ])->passThrough(fn(array $items) => ['key' => 'value', 'id' => 123]);

        self::assertEquals(['id' => '345', 'key' => 'value'], $type->resolve(['id' => '345'], new Context()));

        $justPassThrough = $type->passThrough();
        self::assertEquals(['id' => '345', 'other' => 'passed'], $justPassThrough->resolve(['id' => '345', 'other' => 'passed'], new Context()));
    }

    public function testCorrectFailureOnNull(): void
    {
        $object = ObjectType::make(['name' => StringType::make()]);
        self::assertSame(Value::INVALID, $object->resolve(null, new Context()));
    }

    public function testTypeDefinitionWithDocBlock(): void
    {
        $type = ObjectType::make([
            'id' => Field::ofType(StringType::make())->describe('this is the description')->deprecated(),
            'name' => StringType::make(),
        ]);
        $expectedDescription = <<<DOC
{/**
 * this is the description
 * @deprecated
 */id:string;name:string}
DOC;
        self::assertEquals($expectedDescription, Typescript::fromJsonSchema($type->toDefinition()->input()));
        self::assertEquals($expectedDescription, Typescript::fromJsonSchema($type->toDefinition()->output()));
    }

    public function testParsing(): void
    {
        $type = ObjectType::make([
            'id' => IntType::make()->min(1),
            'name' => StringType::make()->minLength(1)->maxLength(10)->nullable(),
            'opt' => Field::ofType(StringType::make()->nullable())->optional(),
        ]);

        self::assertSame(['id' => 1, 'name' => 'my-name'], $type->resolve(['id' => 1, 'name' => 'my-name', 'other' => true], new Context()));

        self::assertSame(['id' => 123, 'name' => 'my-other'], $type->resolve(GettersMock::standardObject([
            'id' => 123,
            'name' => 'my-other',
            'other' => true,
        ]), new Context()));
    }

    public function testFieldResolver()
    {
        $type = ObjectType::make([
            'id' => Field::ofType(IntType::make()->min(1))->resolvedBy(fn($data) => $data->id()),
        ]);

        $object = new class () {
            public function id(): int
            {
                return 123456;
            }
        };
        self::assertSame(['id' => 123456], $type->resolve($object, new Context()));
    }

    public function testPassThrough()
    {
        $type = ObjectType::make([
            'id' => Field::ofType(IntType::make()->min(1))
                ->resolvedBy(fn($data) => $data['id'] + 2),
        ]);

        self::assertEquals(['id' => 125, 'other' => true], $type->passThrough()->resolve(['id' => 123, 'other' => true], new Context()));
    }

    public function testShowsErrorsForAllFieldsOnFailure(): void
    {
        $schema = ObjectType::make([
            'id' => IntType::make()->min(1),
            'password' => StringType::make()->minLength(10),
        ]);

        $result = $schema->resolve(['id' => 0, 'password' => 'test'], $context = new Context());
        self::assertSame(Value::INVALID, $result);

        self::assertCount(2, $context->getIssues());
    }

}
