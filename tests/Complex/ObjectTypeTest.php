<?php declare(strict_types=1);

namespace Tests\Complex;

use TypescriptSchema\Complex\Field;
use TypescriptSchema\Complex\ObjectType;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Primitives\IntType;
use TypescriptSchema\Primitives\StringType;

class ObjectTypeTest extends TestCase
{

    public function testObjectTypeDefinition() {
        $type = ObjectType::make([
            'id' => StringType::make()->min(1),
            'name' => StringType::make()->min(1)->max(10)->nullable(),
            'opt' => Field::ofType(StringType::make()->nullable())->optional(),
        ]);

        self::assertSame('{id: string; name: string|null; opt?: string|null;}', $type->toDefinition());
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
            'id' => Field::ofType(IntType::make()->min(1))->resolvedBy(fn($key, $data) => $data->id()),
        ]);

        $object = new class () {
            public function id(): int
            {
                return 123456;
            }
        };
        self::assertSame(['id' => 123456], $type->parse($object));
    }

}
