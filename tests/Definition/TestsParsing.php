<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Definition\BaseType;
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
     * @param BaseType $type
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
            $result = $type->safeParse($data);
            self::assertTrue($result->isSuccess(), "Expected success, got failure for: " . Serialize::safeType($data));
        }

        foreach ($failing as $data) {
            $result = $type->safeParse($data);
            self::assertFalse($result->isSuccess(), "Expected failure, got success for: " . Serialize::safeType($data));
        }
    }

    abstract public static function parsingDataProvider(): array;

}