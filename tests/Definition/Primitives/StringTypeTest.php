<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use PHPUnit\Framework\Attributes\DataProvider;
use TypescriptSchema\Data\Enum\ExecutionMode;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Helpers\Context;

class StringTypeTest extends TestCase
{
    public function testTypescriptDefinition(): void
    {
        self::assertSame(['type' => 'string'], StringType::make()->toDefinition()->toInputSchema());
        self::assertSame(['type' => 'string'], StringType::make()->toDefinition()->toOutputSchema());
    }

    private function wrap(mixed $value): array
    {
        return is_array($value) ? $value : [$value];
    }

    #[DataProvider('successfulPassesDataProvider')]
    public function testSuccessfulPasses(Type $type, mixed $successful, mixed $failing = null): void
    {
        $successful = $this->wrap($successful ?? []);
        $failing = $this->wrap($failing ?? []);

        foreach ($successful as $value) {
            $result = Executor::execute($type, $value, new Context(mode: ExecutionMode::PARSE));
            self::assertNotSame(Value::INVALID, $result);
        }

        foreach ($failing as $value) {
            self::assertSame(Value::INVALID,  Executor::execute($type, $value, new Context(mode: ExecutionMode::PARSE)));
        }
    }

    private static function stringable(string $value): \Stringable
    {
        return new class ($value) implements \Stringable
        {
            public function __construct(private readonly string $value)
            {
            }

            public function __toString(): string
            {
                return $this->value;
            }
        };
    }

    public static function successfulPassesDataProvider(): array {
        return [
            'simple string' => [
                StringType::make(),
                'My String',
            ],
            'exact min length' => [
                StringType::make()->minLength(6),
                'String',
                'Strin'
            ],
            'exact max length' => [
                StringType::make()->maxLength(6),
                'String',
                'string+1'
            ],
            'ends with' => [
                StringType::make()->endsWith('.test'),
                'String.test',
                'string.not-test'
            ],
            'start with' => [
                StringType::make()->startsWith('String'),
                ['String.startsWith', 'String'],
                ['other', '']
            ],
            'email' => [
                StringType::make()->email(),
                'test@me.local',
                'test_e.local',
            ],
            'email ending in test' => [
                StringType::make()->endsWith('.test')->email(),
                'test@me.test',
                'test_e.test',
            ],
            'regex' => [
                StringType::make()->regex('/^[a-z]+$/'),
                'mystring',
                'myString',
            ],
            'nullable' => [
                StringType::make()->nullable(),
                [null],
            ],
            'alpha numeric' => [
                StringType::make()->alphaNumeric(),
                ['ThisIsANumericString0123456789', ''],
                ['@', '  a', 'other_']
            ],
            'non empty' => [
                StringType::make()->nonEmpty(),
                '  a',
                [PHP_EOL, '', ' ', '    ', "     \n\r\t\0"]
            ],
            'stringable' => [
                StringType::make()->coerce(),
                self::stringable('value'),
            ],
            'stringable failure without coercion' => [
                StringType::make(),
                [],
                self::stringable('value')
            ],
        ];
    }

}
