<?php declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use TypescriptSchema\Complex\ObjectType;
use TypescriptSchema\Schema;

final class ComplextSchemaTest extends TestCase
{

    private function schema(): ObjectType
    {
        return Schema::object([
            'tuple' => Schema::tuple(
                Schema::string(),
                Schema::literalUnion(['this', 'is', 'a', 'test']),
                Schema::string()->nullable(),
            ),
            'user' => Schema::array(
                Schema::object([
                    'name' => Schema::string(),
                    'age' => Schema::int()->min(0),
                    'email' => Schema::string()->nullable()->email()->endsWith('.test')->lowerCase(),
                ])
            ),
        ]);
    }

    public function testSchema()
    {
        $result = $this->schema()->safeParse([
            'tuple' => ['string', 'is', null],
            'user' => [
                (object) [
                    'name' => 'string',
                    'age' => 24,
                    'email' => 'test@domain.test'
                ]
            ]
        ]);

        self::assertTrue($result->isSuccess());
    }

}