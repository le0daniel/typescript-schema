<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use TypescriptSchema\Definition\Complex\RecordType;
use TypescriptSchema\Definition\Primitives\LiteralType;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Tests\Definition\TestsParsing;
use TypescriptSchema\Tests\Mocks\TraversableMock;
use TypescriptSchema\Tests\Mocks\UnitEnumMock;
use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Utils\Typescript;

class RecordTypeTest extends TestCase
{
    use TestsParsing;

    public function testDefinition()
    {
        $same = RecordType::make(StringType::make());
        self::assertEquals("{[key: string]:string}", Typescript::fromJsonSchema($same->toDefinition()->input()));
        self::assertEquals("{[key: string]:string}", Typescript::fromJsonSchema($same->toDefinition()->output()));

        $different = RecordType::make(LiteralType::make(UnitEnumMock::SUCCESS));
        self::assertEquals("{[key: string]:'SUCCESS'}", Typescript::fromJsonSchema($different->toDefinition()->input()));
        self::assertEquals("{[key: string]:'SUCCESS'}", Typescript::fromJsonSchema($different->toDefinition()->output()));
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

        self::assertFailure($record->resolve(['name' => 'value', 'email' => null], new Context()));
        self::assertIssuesCount(1, $record,['name' => 'value', 'email' => null]);

        $record->resolve(['name' => 'value', 'email' => null], $context = new Context());
        self::assertEquals(['email'], $context->getIssues()[0]->getPath());
    }

    public function testNumberOfIssues()
    {
        $type = RecordType::make(StringType::make()->nonEmpty());
        self::assertIssuesCount(2, $type, ['name' => 123, 'email' => '']);
    }
}
