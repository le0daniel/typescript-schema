<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Options;
use TypescriptSchema\Definition\Complex\Field;
use TypescriptSchema\Definition\Complex\ObjectType;
use TypescriptSchema\Definition\Primitives\AnyType;
use TypescriptSchema\Definition\Primitives\IntType;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Definition\Schema;
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

    public function testAnyOnField(): void
    {
        $object = ObjectType::make(['name' => new AnyType()]);
        self::assertEquals('{name:any}', Typescript::fromJsonSchema($object->toDefinition()->input()));
    }

    public function testExtending()
    {
        $object = ObjectType::make(['name' => StringType::make()]);
        self::assertEquals(['name' => 'test'], $object->toSchema()->parse(['name' => 'test'])->getData());

        self::assertEquals(['name' => 'test', 'other' => 'wow'], $object->extend([
            'other' => StringType::make(),
        ])->toSchema()->parse(['name' => 'test', 'other' => 'wow'])->getData());
    }

    public function testResolverWithContext()
    {
        $object = ObjectType::make([
            'name' => Schema::field(new StringType())->resolvedBy(fn($_, $__, array $context) => $context['name'])]
        )->toSchema();

        self::assertSame(
            ['name' => 'test'],
            $object->parse(['name' => 'something'], new Options(context: ['name' => 'test']))->getData()
        );

        self::assertSame(
            ['name' => 'other'],
            $object->parse([], new Options(context: ['name' => 'other']))->getData()
        );
    }

    public function testRemoveFields(): void
    {
        $object = ObjectType::make(['name' => new AnyType()]);
        $objectWithoutName = $object->removeFields(['name']);

        self::assertNotSame($object, $objectWithoutName);
        self::assertFalse($object->isEmpty());
        self::assertTrue($objectWithoutName->isEmpty());

        self::assertEquals('{}', Typescript::fromJsonSchema($objectWithoutName->toDefinition()->input()));
        self::assertEquals('{name:any}', Typescript::fromJsonSchema($object->toDefinition()->input()));
    }

    public function testNotEmptyValidator()
    {
        $object = ObjectType::make(['name?' => StringType::make()]);
        self::assertSame([], $object->parse([], new Context()));
        self::assertSame(Value::INVALID, $object->notEmpty()->parse([], new Context()));
    }

    public function testExtend(): void
    {
        $object = ObjectType::make(['name' => StringType::make()]);
        $clone = $object->extend(['other' => StringType::make()]);

        self::assertNotSame($object, $clone);
        self::assertSame('{name:string}', Typescript::fromJsonSchema($object->toDefinition()->input()));
        self::assertSame('{name:string;other:string}', Typescript::fromJsonSchema($clone->toDefinition()->input()));
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

    public function testOptionalByName()
    {
        $type = ObjectType::make([
            'id' => StringType::make(),
            'name?' => StringType::make()->nullable(),
            'other?' => Field::ofType(StringType::make()),
        ]);

        self::assertSame('{id:string;name?:string|null;other?:string}', Typescript::fromJsonSchema($type->toDefinition()->output()));
        self::assertSame('{id:string;name?:string|null;other?:string}', Typescript::fromJsonSchema($type->toDefinition()->input()));
    }

    public function testPassThroughAsClosure(): void
    {
        $type = ObjectType::make([
            'id' => StringType::make(),
        ])->passThrough(fn(array $items) => ['key' => 'value', 'id' => 123]);

        self::assertEquals(['id' => '345', 'key' => 'value'], $type->parse(['id' => '345'], new Context()));

        $justPassThrough = $type->passThrough();
        self::assertEquals(['id' => '345', 'other' => 'passed'], $justPassThrough->parse(['id' => '345', 'other' => 'passed'], new Context()));
    }

    public function testCorrectFailureOnNull(): void
    {
        $object = ObjectType::make(['name' => StringType::make()]);
        self::assertSame(Value::INVALID, $object->parse(null, new Context()));
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

        self::assertSame(['id' => 1, 'name' => 'my-name'], $type->parse(['id' => 1, 'name' => 'my-name', 'other' => true], new Context()));

        self::assertSame(['id' => 123, 'name' => 'my-other'], $type->parse(GettersMock::standardObject([
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
        self::assertSame(['id' => 123456], $type->parse($object, new Context()));
    }

    public function testPassThrough()
    {
        $type = ObjectType::make([
            'id' => Field::ofType(IntType::make()->min(1))
                ->resolvedBy(fn($data) => $data['id'] + 2),
        ]);

        self::assertEquals(['id' => 125, 'other' => true], $type->passThrough()->parse(['id' => 123, 'other' => true], new Context()));
    }

    public function testShowsErrorsForAllFieldsOnFailure(): void
    {
        $schema = ObjectType::make([
            'id' => IntType::make()->min(1),
            'password' => StringType::make()->minLength(10),
        ]);

        $result = $schema->parse(['id' => 0, 'password' => 'test'], $context = new Context());
        self::assertSame(Value::INVALID, $result);

        self::assertCount(2, $context->getIssues());
    }

}
