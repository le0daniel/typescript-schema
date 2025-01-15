<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Utils;

use TypescriptSchema\Tests\Mocks\CastableFrom;
use TypescriptSchema\Tests\Mocks\DeepValueObject;
use TypescriptSchema\Tests\Mocks\IdTypeMock;
use TypescriptSchema\Tests\Mocks\ValueObjectWithConstructor;
use TypescriptSchema\Tests\Mocks\ValueObjectWithoutConstructor;
use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Utils\ObjectCaster;

class ValueObjectCasterTest extends TestCase
{

    public function testPrimitiveValueObjectCasting()
    {
        $valueObject = ObjectCaster::cast(IdTypeMock::class, 1);
        self::assertInstanceOf(IdTypeMock::class, $valueObject);
        self::assertEquals(1, $valueObject->id);
    }

    public function testCast()
    {
        self::assertInstanceOf(
            ValueObjectWithoutConstructor::class,
            ObjectCaster::cast(ValueObjectWithoutConstructor::class, [
                'name' => 'Test',
                'lastName' => 'Wow'
            ])
        );

        self::assertInstanceOf(
            ValueObjectWithConstructor::class,
            ObjectCaster::cast(ValueObjectWithConstructor::class, [
                'name' => 'Test',
            ])
        );
    }

    public function testDeepCasting()
    {
        $valueObject = ObjectCaster::cast(DeepValueObject::class, []);
        self::assertInstanceOf(DeepValueObject::class, $valueObject);
        self::assertNull($valueObject->with);
        self::assertNull($valueObject->without);

        $valueObject = ObjectCaster::cast(DeepValueObject::class, ['with' => ['name' => 'Test']]);
        self::assertInstanceOf(DeepValueObject::class, $valueObject);
        self::assertNotNull($valueObject->with);
        self::assertNull($valueObject->without);

        $valueObject = ObjectCaster::cast(DeepValueObject::class, ['without' => ['name' => 'Test', 'lastName' => 'other']]);
        self::assertInstanceOf(DeepValueObject::class, $valueObject);
        self::assertNull($valueObject->with);
        self::assertNotNull($valueObject->without);

        $valueObject = ObjectCaster::cast(DeepValueObject::class, ['without' => ['name' => 'Test', 'lastName' => 'other'], 'with' => ['name' => 'other']]);
        self::assertInstanceOf(DeepValueObject::class, $valueObject);
        self::assertNotNull($valueObject->with);
        self::assertNotNull($valueObject->without);
    }

    public function testValueObjectWithFromMethod()
    {
        $valueObject = ObjectCaster::cast(CastableFrom::class, [
            'name' => 'Test',
        ]);

        self::assertInstanceOf(CastableFrom::class, $valueObject);
        self::assertEquals('Test', $valueObject->otherName);

        $valueObject = ObjectCaster::cast(CastableFrom::class, [
            'otherName' => 'Test',
        ]);

        self::assertInstanceOf(CastableFrom::class, $valueObject);
        self::assertEquals('Test', $valueObject->otherName);
    }
}
