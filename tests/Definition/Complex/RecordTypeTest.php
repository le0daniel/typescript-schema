<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use TypescriptSchema\Definition\Complex\RecordType;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\LiteralType;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Tests\Definition\TestsParsing;
use TypescriptSchema\Tests\Mocks\TraversableMock;
use TypescriptSchema\Tests\Mocks\UnitEnumMock;

class RecordTypeTest extends TestCase
{
    use TestsParsing;

    public function testDefinition()
    {
        $same = RecordType::make(StringType::make());
        self::assertEquals('Record<string,string>', $same->toDefinition()->input);
        self::assertEquals('Record<string,string>', $same->toDefinition()->output);

        $different = RecordType::make(LiteralType::make(UnitEnumMock::SUCCESS));
        self::assertEquals("Record<string,'SUCCESS'>", $different->toDefinition()->input);
        self::assertEquals('Record<string,never>', $different->toDefinition()->output);
    }

    public static function parsingDataProvider(): array
    {
        return [
            'array' => [
                RecordType::make(StringType::make()),
                [
                    ['name' => 'value', 'email' => 'something']
                ],
                [
                    ['value', 'email']
                ]
            ],
            'iterator' => [
                RecordType::make(StringType::make()),
                [
                    new TraversableMock([
                        'name' => 'value',
                        'email' => 'something',
                        'wow' => 'new',
                    ])
                ],
                [
                    new TraversableMock(['one', 'two'])
                ]
            ],
        ];
    }

    public function testPathOfIssues()
    {
        $record = RecordType::make(StringType::make());
        $result = $record->safeParse(['name' => 'value', 'email' => null]);

        self::assertCount(1, $result->issues);
        self::assertEquals(['email'], $result->issues[0]->getPath());
    }
}
