<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition;

use PHPUnit\Framework\Attributes\DataProvider;
use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\ExecutionMode;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Utils\Serialize;

/**
 * @mixin TestCase
 */
trait TestsParsing
{
    private function wrap(mixed $data): array
    {
        return is_array($data) ? $data : [$data];
    }

    /**
     * @param Type $type
     * @param mixed $successful
     * @param mixed $failing
     * @return void
     */
    #[DataProvider('parsingDataProvider')]
    public function testsParsing(Type $type, mixed $successful, mixed $failing = null): void
    {
        $successful = $this->wrap($successful ?? []);
        $failing = $this->wrap($failing ?? []);

        foreach ($successful as $data) {
            $result = Executor::execute($type, $data, new Context(mode: ExecutionMode::PARSE));
            self::assertTrue($result !== Value::INVALID, "Expected success, got failure for: " . Serialize::safeType($data));
        }

        foreach ($failing as $data) {
            $result = Executor::execute($type, $data, new Context(mode: ExecutionMode::PARSE));
            self::assertTrue($result === Value::INVALID,
                "Expected failure, got success for: " . json_encode(['input' => $data, 'output' => Serialize::safeType($result)], JSON_PRETTY_PRINT)
            );
        }
    }

    abstract public static function parsingDataProvider(): array;

}