<?php declare(strict_types=1);

namespace TypescriptSchema\Tests;

use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\ExecutionMode;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Result;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\Context;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{

    protected static function assertFailure(mixed $result): void
    {
        if ($result instanceof Result) {
            self::assertTrue($result->isFailure());
            return;
        }

        self::assertSame(Value::INVALID, $result);
    }

    protected static function assertSuccess(mixed $result): void
    {
        if ($result instanceof Result) {
            self::assertTrue($result->isSuccess());
            return;
        }

        self::assertNotInstanceOf(Value::class, $result);
    }

    protected static function assertIssuesCount(int $expected, Type $type, mixed $data, ExecutionMode $mode = ExecutionMode::SERIALIZE): void
    {
        $result = Executor::execute($type, $data, $context = new Context($mode));
        self::assertSame(Value::INVALID, $result);
        self::assertCount($expected, $context->getIssues());
    }

}