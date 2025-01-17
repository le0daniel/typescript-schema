<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Utils;

use TypescriptSchema\Tests\Mocks\IntBackedEnumMock;
use TypescriptSchema\Tests\Mocks\StringBackedEnumMock;
use TypescriptSchema\Tests\Mocks\UnitEnumMock;
use TypescriptSchema\Utils\Typescript;
use TypescriptSchema\Tests\TestCase;

class TypescriptTest extends TestCase
{
    public function testFromSchema(): void
    {
        self::assertSame("any", Typescript::fromJsonSchema([]));
        self::assertSame("'123'", Typescript::fromJsonSchema(['const' => "123"]));
        self::assertSame("'123'|'456'", Typescript::fromJsonSchema(['enum' => ["123", "456"]]));

        self::assertEquals('string', Typescript::fromJsonSchema(['type' => "string"]));
        self::assertEquals('number', Typescript::fromJsonSchema(['type' => "integer"]));
        self::assertEquals('number', Typescript::fromJsonSchema(['type' => "number"]));
        self::assertEquals('boolean', Typescript::fromJsonSchema(['type' => "boolean"]));
        self::assertEquals('null', Typescript::fromJsonSchema(['type' => "null"]));

        self::assertEquals('Array<number>', Typescript::fromJsonSchema(['type' => "array", 'items' => ['type' => 'integer']]));

        self::assertEquals('[number,string,number]', Typescript::fromJsonSchema(['type' => "array", 'prefixItems' => [
            ['type' => 'integer'],
            ['type' => 'string'],
            ['type' => 'number'],
        ]]));

        self::assertEquals('number|null', Typescript::fromJsonSchema([
            'anyOf' => [
                ['type' => 'integer'],
                ['type' => 'null'],
            ]
        ]));

        self::assertEquals('number|null', Typescript::fromJsonSchema([
            'oneOf' => [
                ['type' => 'integer'],
                ['type' => 'null'],
            ]
        ]));

        self::assertEquals("{name?:boolean;[key: string]:unknown}",  Typescript::fromJsonSchema([
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'boolean']
            ]
        ]));

        self::assertEquals("{name:boolean;[key: string]:unknown}",  Typescript::fromJsonSchema([
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'boolean']
            ],
            "required" => ['name']
        ]));

        self::assertEquals("{name:boolean}",  Typescript::fromJsonSchema([
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'boolean']
            ],
            "required" => ['name'],
            'additionalProperties' => false
        ]));

        self::assertEquals("{name:boolean;[key: string]:true}",  Typescript::fromJsonSchema([
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'boolean']
            ],
            "required" => ['name'],
            'additionalProperties' => ['const' => true]
        ]));

        self::assertEquals("{/**\n * @deprecated\n */name:boolean}",  Typescript::fromJsonSchema([
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'boolean', 'deprecated' => true],
            ],
            "required" => ['name'],
            'additionalProperties' => false
        ]));
    }
}
