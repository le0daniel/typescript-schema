<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Integration;

use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Definition\Schema;
use TypescriptSchema\Exceptions\Issue;
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

}