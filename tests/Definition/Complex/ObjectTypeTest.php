<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Complex\Field;
use TypescriptSchema\Definition\Complex\ObjectType;
use TypescriptSchema\Definition\Primitives\IntType;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Exceptions\ParsingException;

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

        self::assertSame('{id: string; name: string|null; opt?: string|null;}', $type->toOutputDefinition());
        self::assertTrue($type->toInputDefinition() === $type->toOutputDefinition());
        self::assertSame('{id: string; name: string|null; opt?: string|null; [key: string]: unknown;}', $type->passThrough()->toOutputDefinition());
        self::assertTrue($type->passThrough()->toInputDefinition() === $type->passThrough()->toOutputDefinition());
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
 * 
 * @deprecated
 */id: string; name: string;}
DOC;
        self::assertEquals($expectedDescription, $type->toInputDefinition());
        self::assertEquals($expectedDescription, $type->toOutputDefinition());
    }

    public function testParsing(): void
    {
        $type = ObjectType::make([
            'id' => IntType::make()->min(1),
            'name' => StringType::make()->min(1)->max(10)->nullable(),
            'opt' => Field::ofType(StringType::make()->nullable())->optional(),
        ]);

        self::assertSame(['id' => 1, 'name' => 'my-name'], $type->parse(['id' => 1, 'name' => 'my-name', 'other' => true]));

        $object = new \stdClass();
        $object->name = 'my-other';
        $object->other = true;
        $object->id = 123;

        self::assertSame(['id' => 123, 'name' => 'my-other'], $type->parse($object));
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
        self::assertSame(['id' => 123456], $type->parse($object));
    }

    public function testPassThrough()
    {
        $type = ObjectType::make([
            'id' => Field::ofType(IntType::make()->min(1))
                ->resolvedBy(fn($data) => $data['id'] + 2),
        ]);

        try {
            self::assertEquals(['id' => 125, 'other' => true], $type->passThrough()->parse(['id' => 123, 'other' => true]));
        } catch (ParsingException $exception) {
            var_dump($exception->issues);
        }
    }

}
