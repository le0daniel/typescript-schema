<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Utils;

use Exception;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\SimpleLoaderLocalizer;
use TypescriptSchema\Utils\Issues;
use TypescriptSchema\Tests\TestCase;

class IssuesTest extends TestCase
{

    public function testSerialize()
    {
        $issue = Issue::generic();
        self::assertEquals([
            [
                'message' => 'Invalid value',
                'path' => []
            ]
        ], Issues::serialize([$issue], new SimpleLoaderLocalizer(), 'en'));

        self::assertEquals([
            [
                'message' => 'Invalid value',
                'path' => []
            ]
        ], Issues::serialize([$issue], new SimpleLoaderLocalizer(), 'en', true));

        $serializedWithException = Issues::serialize([Issue::captureThrowable(new Exception('my message'))], new SimpleLoaderLocalizer(), 'en', true);

        self::assertArrayIsIdenticalToArrayIgnoringListOfKeys(
            [
                'message' => 'Invalid value',
                'path' => [],
            ],
            $serializedWithException[0],
            ['exception']
        );

        self::assertArrayHasKey('exception', $serializedWithException[0]);
        self::assertEquals('my message', $serializedWithException[0]['exception']['message']);
    }

    public function testSerializeGrouped()
    {
        $issues = [
            Issue::generic(),
            Issue::captureThrowable(new Exception('my message')),
            Issue::custom('Wow, its invalid.'),
            Issue::custom('Wow, its invalid item.', path: ['item', 1, 'name'])
        ];

        self::assertEquals([
            '' => ['Invalid value', 'Invalid value', 'Wow, its invalid.',],
            'item.1.name' => ['Wow, its invalid item.']
        ], Issues::serializeGrouped($issues, new SimpleLoaderLocalizer(), 'en'));
    }
}
