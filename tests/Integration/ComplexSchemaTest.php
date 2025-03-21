<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Integration;

use DateTimeImmutable;
use RuntimeException;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Resource;
use TypescriptSchema\Definition\Schema;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Tests\Mocks\IdTypeMock;
use TypescriptSchema\Tests\Mocks\UnitEnumMock;
use TypescriptSchema\Tests\Mocks\ValueObjectWithConstructor;
use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Utils\Typescript;

final class ComplexSchemaTest extends TestCase
{
    public function testObjectRefinement(): void
    {
        $schema = Schema::make(
            Schema::object([
                'password' => Schema::string()->minLength(8),
                'password_confirm' => Schema::string()->minLength(8),
            ])->refine(
                fn(array $data): bool => $data['password'] === $data['password_confirm'],
                fn() => Issue::custom('Password did not match confirmed password.', path: ['password']),
            )
        );

        self::assertTrue(
            $schema->parse(['password' => 'super-secret', 'password_confirm' => 'super-secret'])->isSuccess()
        );
        self::assertTrue(
            $schema->parse(['password' => 'super-secret', 'password_confirm' => 'super-secret-but-different'])->isFailure()
        );

        $result = $schema->parse(['password' => 'super-secret', 'password_confirm' => 'super-secret-but-different']);
        self::assertCount(1, $result->issues);
        self::assertEquals('Password did not match confirmed password.', $result->issues[0]->getMessage());
        self::assertEquals(['password'], $result->issues[0]->getPath());
    }

    public function testTransform()
    {
        $schema = Schema::make(
            Schema::object([
                'username' => Schema::string(),
                'age' => Schema::int()->min(0),
                'email' => Schema::string()->nullable()->email()->endsWith('.test'),
            ])->transform(function (array $user): string {
                return "{$user['username']}({$user['age']}): {$user['email']}";
            }, ['type' => 'string'])
        );

        self::assertEquals(
            "leodaniel(29): test@me.test",
            $schema->parse(['username' => 'leodaniel', 'age' => 29, 'email' => 'test@me.test'])->getData()
        );

        self::assertEquals('{username:string;age:number;email:string|null}', Typescript::fromJsonSchema($schema->toDefinition()->input()));
        self::assertEquals('string', Typescript::fromJsonSchema($schema->toDefinition()->output()));
    }

    public function testChainingOfTransformAndRefine(): void
    {
        $schema = Schema::string()
            ->transform(fn(string $name): int => strlen($name))
            ->refine(fn(int $length): bool => $length > 10)
            ->refine(fn() => true)
            ->transform(fn(int $length): string => (string)$length)
            ->refine(fn($val) => $val === "11");

        self::assertEquals("11", $schema->parse('stringal911', new Context()));
    }

    public function testTupleParsing()
    {
        $tuple = Schema::tuple([
            Schema::string(),
            Schema::string()->nullable(),
            Schema::int(),
        ]);

        [$name, $username, $age] = $tuple->parse(['Hans', null, 99], new Context());
        self::assertEquals(['Hans', null, 99], [$name, $username, $age]);
    }

    public function testNullErrorBoundaries()
    {
        $schema = Schema::make($type = Schema::array(
            Schema::object([
                'name' => Schema::string(),
            ])->nullable()
        ));

        self::assertFailure($schema->parse([
            [],
            ['name' => 0],
            ['name' => 'hans']
        ]));

        self::assertSuccess($schema->parse([
            ['name' => 'okey'],
            ['name' => 'wow'],
            ['name' => 'hans']
        ]));

        self::assertSuccess(
            $type->parse([
                [],
                ['name' => 0],
                ['name' => 'hans']
            ], new Context(allowPartialFailures: true))
        );
    }

    public function testDefaultValues(): void
    {
        $schema = Schema::make(
            Schema::object([
                'string' => Schema::string()->defaultValue('my-string'),
                'boolean' => Schema::bool()->defaultValue(true),
                'integer' => Schema::int()->defaultValue(99),
                'float' => Schema::number()->defaultValue(109.9),
                'literal' => Schema::literal('test')->defaultValue('test'),
                'date' => Schema::dateTime('Y-m-d')->defaultValue('2021-09-27'),
                'dateImmutableDefault' => Schema::dateTime('Y-m-d')
                    ->defaultValue(DateTimeImmutable::createFromFormat('Y-m-d', '2022-02-25')),
                'enum' => Schema::enum(UnitEnumMock::class)->defaultValue('SUCCESS'),
                'enumAsEnum' => Schema::enum(UnitEnumMock::class)->defaultValue(UnitEnumMock::FAILURE),
            ])
        );

        self::assertSuccess($result = $schema->serialize([]));
        self::assertEquals([
            'string' => 'my-string',
            'boolean' => true,
            'integer' => 99,
            'float' => 109.9,
            'literal' => 'test',
            'date' => '2021-09-27',
            'dateImmutableDefault' => '2022-02-25',
            'enum' => 'SUCCESS',
            'enumAsEnum' => 'FAILURE'
        ], $result->getData());
    }

    public function testDeeperSchema()
    {
        $schema = Schema::make(Schema::object([
            'tuple' => Schema::tuple([
                Schema::string(),
                Schema::literalUnion('this', 'is', 'a', 'test'),
                Schema::string()->nullable(),
            ]),
            'user' => Schema::array(
                Schema::object([
                    'name' => Schema::string(),
                    'age' => Schema::int()->min(0),
                    'email' => Schema::string()->nullable()->email()->endsWith('.test'),
                ])
            ),
        ]));

        $result = $schema->parseOrFail([
            'tuple' => ['string', 'is', null],
            'user' => [
                (object)[
                    'name' => 'string',
                    'age' => 24,
                    'email' => 'test@domain.test'
                ]
            ]
        ]);

        self::assertSuccess($result);
    }

    public function testSchemaWithCasting(): void
    {
        $schema = Schema::object([
            'name' => Schema::string(),
        ])->toSchema();

        $result = $schema->parse(['name' => 'test']);
        self::assertTrue($result->isSuccess());
        self::assertInstanceOf(ValueObjectWithConstructor::class, $result->castInto(ValueObjectWithConstructor::class));
    }

    public function testSchemaWithInvalidCasting(): void
    {
        $schema = Schema::object([
            'name?' => Schema::string(),
        ])->toSchema();

        $result = $schema->parse([]);
        self::assertTrue($result->isSuccess());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to cast the data into TypescriptSchema\Tests\Mocks\ValueObjectWithConstructor.');
        $result->castInto(ValueObjectWithConstructor::class);
    }

    public function testCastIntoWithNull()
    {
        $schema = Schema::int()->min(1)->nullable()->toSchema();
        self::assertNull($schema->parse(null)->castInto(IdTypeMock::class));

        self::assertInstanceOf(
            IdTypeMock::class,
            $schema->parse(1)->castInto(IdTypeMock::class)
        );
    }

    public function testWithUndefinedResolver()
    {
        $schema = Schema::object([
            'name' => Schema::string(),
            'optional?' => Schema::field(Schema::string())
                ->resolvedBy(fn(array $data) => $data['optional'] ?? Value::UNDEFINED),
        ])->toSchema();

        self::assertEquals(['name' => 'test'], $schema->parse(['name' => 'test'])->getData());
        self::assertEquals(['name' => 'test', 'optional' => 'string'], $schema->parse(['name' => 'test', 'optional' => 'string'])->getData());
    }

    public function testEmptyNamedExtraction(): void
    {
        $schema = Schema::object([
            'name' => Schema::string(),
            'optional?' => Schema::field(Schema::string())
                ->resolvedBy(fn(array $data) => $data['optional'] ?? Value::UNDEFINED),
        ])->toSchema();

        self::assertEmpty($schema->getNamedTypes());
    }

    public function testNamedExtraction(): void
    {
        $schema = Schema::object([
            'name' => Schema::named('name', Schema::string()),
            'optional?' => Schema::field(Schema::string())
                ->resolvedBy(fn(array $data) => $data['optional'] ?? Value::UNDEFINED),
        ])->toSchema();

        self::assertNotEmpty($schema->getNamedTypes());
        self::assertCount(1, $schema->getNamedTypes());
        self::assertEquals('string', Typescript::fromJsonSchema($schema->getNamedTypes()['name']->toDefinition()->output()));
    }

    public function testNamedExtractionDeep(): void
    {
        $schema = Schema::object([
            'user' => Schema::named('User', Schema::object([
                'name' => Schema::string(),
                'age' => Schema::int()->min(0),
                'address' => Schema::named('Address', Schema::object([
                    'street' => Schema::string(),
                ])->nullable())
            ])),
        ])->toSchema();

        self::assertNotEmpty($schema->getNamedTypes());
        self::assertCount(2, $schema->getNamedTypes());
        self::assertEquals('{name:string;age:number;address:{street:string}|null}', Typescript::fromJsonSchema($schema->getNamedTypes()['User']->toDefinition()->output()));
        self::assertEquals('{street:string}', Typescript::fromJsonSchema($schema->getNamedTypes()['Address']->toDefinition()->output()));
    }

    public function testResources()
    {
        $address = new readonly class extends Resource
        {
            public static function type(): Type
            {
                return Schema::object([
                    'street' => Schema::string(),
                ]);
            }
        };

        $user = new readonly class extends Resource {
            public static function type(): Type
            {
                return Schema::named('User', Schema::object([
                    'name' => Schema::string(),
                    'age' => Schema::int()->min(0),
                ]));
            }
        };

        $schema = Schema::object([
            'user' => Schema::resource($user::class),
            'address' => Schema::named('Address', Schema::resource($address::class)->nullable()),
        ])->toSchema();

        self::assertEquals(
            '{user:{name:string;age:number};address:{street:string}|null}', Typescript::fromJsonSchema($schema->toDefinition()->output())
        );
    }

}