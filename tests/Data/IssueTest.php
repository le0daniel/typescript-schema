<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Data;

use PHPUnit\Framework\TestCase;
use stdClass;
use TypescriptSchema\Exceptions\Issue;

class IssueTest extends TestCase
{
    public function testInvalidType(): void
    {
        self::assertEquals(
            "Expected string, got string<'this is a value'>",
            Issue::invalidType('string', 'this is a value')->getMessage()
        );

        self::assertEquals(
            "Expected string, got int<123>",
            Issue::invalidType('string', 123)->getMessage()
        );

        self::assertEquals(
            "Expected string, got object",
            Issue::invalidType('string', new stdClass())->getMessage()
        );

        self::assertEquals(
            "Expected string, got object<Issue>",
            Issue::invalidType('string', Issue::invalidType('', ''))->getMessage()
        );

        self::assertEquals(
            "Expected string, got array",
            Issue::invalidType('string', [])->getMessage()
        );

        self::assertEquals(
            "Expected string, got bool<false>",
            Issue::invalidType('string', false)->getMessage()
        );

        self::assertEquals(
            "Expected string, got NULL",
            Issue::invalidType('string', null)->getMessage()
        );
    }

}
