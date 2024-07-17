<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Integration;

use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Complex\Field;
use TypescriptSchema\Schema;
use TypescriptSchema\Tests\Mocks\UnitEnumMock;

final class ComplexSchemaGeneration extends TestCase
{

    public function testSchemaGeneration()
    {
        $schema = Schema::object([
            'tuple' => Schema::tuple(
                Schema::string(),
                Schema::object(['type' => Schema::literal('wow')])->passThrough(),
                Schema::int(),
            ),
            'users' => Schema::array(
                Schema::object([
                    'name' => Schema::string(),
                    'age' => Schema::int()->nullable(),
                    'email' => Field::ofType(Schema::string())->optional(),
                ]),
            ),
            'logs' => Schema::record(Schema::string()),
            'searchResults' => Schema::discriminatedUnion('type',
                Schema::object(['type' => Schema::literal('book')]),
                Schema::object(['type' => Schema::literal('user')]),
            ),
            'union' => Schema::union(Schema::string()->nullable(), Schema::int()),
            'enum' => Schema::enum(UnitEnumMock::class)->asString(),
            'boolean' => Schema::bool(),
        ]);

        self::assertEquals(<<<TYPESCRIPT
{tuple: [string, {type: 'wow'; [key: string]: unknown;}, number]; users: Array<{name: string; age: number|null; email?: string;}>; logs: Record<string,string>; searchResults: {type: 'book';}|{type: 'user';}; union: string|null|number; enum: 'SUCCESS'|'FAILURE'; boolean: bool;}
TYPESCRIPT, $schema->toDefinition()->input
);
    }

}