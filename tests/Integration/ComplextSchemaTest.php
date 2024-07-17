<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Integration;

use PHPUnit\Framework\TestCase;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Schema;

final class ComplextSchemaTest extends TestCase
{
    public function testObjectRefinement(): void
    {
        $schema = Schema::object([
            'password' => Schema::string()->min(8),
            'password_confirm' => Schema::string()->min(8),
        ])->refine(
            fn(array $data): bool => $data['password'] === $data['password_confirm'],
            fn() => Issue::custom('Password did not match confirmed password.', path: ['password']),
        );

        $schema->parse(['password' => 'super-secret', 'password_confirm' => 'super-secret']);
        $invalid = $schema->safeParse(['password' => 'super-secret', 'password_confirm' => 'super-secret-but-different']);

        self::assertFalse($invalid->isSuccess());
        self::assertCount(1, $invalid->issues);
        self::assertEquals('Password did not match confirmed password.', $invalid->issues[0]->getMessage());
        self::assertEquals(['password'], $invalid->issues[0]->getPath());
    }

    public function testTransform()
    {
        $schema = Schema::object([
            'username' => Schema::string(),
            'age' => Schema::int()->min(0),
            'email' => Schema::string()->nullable()->email()->endsWith('.test')->lowerCase(),
        ])->transform(function (array $user): string {
            return "{$user['username']}({$user['age']}): {$user['email']}";
        }, 'string');

        self::assertEquals(
            "leodaniel(29): test@me.test",
            $schema->parse(['username' => 'leodaniel', 'age' => 29, 'email' => 'test@me.test'])
        );
    }

    public function testChainingOfTransformAndRefine(): void
    {
        $schema = Schema::string()
            ->transform(fn(string $name): int => strlen($name))
            ->refine(fn(int $length): bool => $length > 10)
            ->refine(fn() => true)
            ->transform(fn(int $length): string => (string) $length)
            ->refine(fn($val) => $val === "11");

        self::assertEquals("11", $schema->safeParse('stringal911')->getData());
    }

    public function testTupleParsing()
    {
        $tuple = Schema::tuple(
            Schema::string(),
            Schema::string()->nullable(),
            Schema::int(),
        );

        [$name, $username, $age] = $tuple->parse(['Hans', null, 99]);
        self::assertEquals(['Hans', null, 99], [$name, $username, $age]);
    }

    public function testNullErrorBoundaries()
    {
        $schema = Schema::array(
            Schema::object([
                'name' => Schema::string(),
            ])->nullable()
        );

        $result = $schema->safeParse([
            [],
            ['name' => 0],
            ['name' => 'hans']
        ], true);

        self::assertTrue($result->isPartial());
        self::assertEquals([null, null, ['name' => 'hans']], $result->getData());
        self::assertNull($schema->safeParse([['name' => null]])->getData());
    }

    public function testDeeperSchema()
    {
        $schema = Schema::object([
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

        $result = $schema->safeParse([
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