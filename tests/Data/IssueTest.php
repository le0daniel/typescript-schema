<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Data;

use PHPUnit\Framework\TestCase;
use stdClass;
use TypescriptSchema\Data\Enum\SerializationMode;
use TypescriptSchema\Exceptions\Issue;

class IssueTest extends TestCase
{

    public function testSerialization(): void
    {
        $issue = Issue::custom('My message', ['message' => 'overwrite', 'other' => 'value'], ['root']);

        self::assertEquals([
            'type' => 'CUSTOM',
            'message' => 'My message',
            'other' => 'value',
            'path' => ['root']
        ], $issue->jsonSerialize());

        self::assertEquals([
            'type' => 'CUSTOM',
            'message' => 'My message',
            'other' => 'value',
            'path' => ['user', 'root']
        ], $issue->setBasePath(['user'])->jsonSerialize());
    }

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

    public function testSerializationModeDefault()
    {
        $issue = Issue::captureThrowable(new \RuntimeException('This is my message'));

        self::assertEquals([
            'type' => 'INTERNAL_ERROR',
            'message' => 'Internal error',
            'path' => [],
        ], $issue->toArray());

        self::assertEquals([
            'type' => 'INTERNAL_ERROR',
            'message' => 'Internal error',
            'path' => [],
        ], $issue->toArray(SerializationMode::ALL));

        self::assertTrue(isset($issue->toArray(SerializationMode::ALL_WITH_DEBUG)['previous']));
    }

    public static function testSerializationModeWithAll()
    {
        $issue = Issue::custom('My message', ['actual' => 'super secret']);

        self::assertEquals([
            'type' => 'CUSTOM',
            'message' => 'My message',
            'path' => [],
        ], $issue->toArray());

        self::assertEquals([
            'type' => 'CUSTOM',
            'message' => 'My message',
            'path' => [],
            'actual' => 'super secret',
        ], $issue->toArray(SerializationMode::ALL));

        self::assertEquals([
            'type' => 'CUSTOM',
            'message' => 'My message',
            'path' => [],
            'actual' => 'super secret',
        ], $issue->toArray(SerializationMode::ALL_WITH_DEBUG));
    }

}
