<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\DateTimeType;
use TypescriptSchema\Tests\Definition\TestsParsing;

class DateTimeTypeTest extends TestCase
{
    use TestsParsing;

    public function testDefinition()
    {
        self::assertEquals('string', DateTimeType::make()->toInputDefinition());
        self::assertEquals('{date: string, timezone_type: number, timezone: string}', DateTimeType::make()->toOutputDefinition());
        self::assertEquals('string', DateTimeType::make()->toFormattedString()->toOutputDefinition());
        self::assertEquals('string|null', DateTimeType::make()->toFormattedString()->nullable()->toOutputDefinition());
    }

    public function testsReturnsInstanceOfDateTimeImmutable(): void
    {
        $type = DateTimeType::make('Y-m-d H:i:s');
        self::assertInstanceOf(DateTimeImmutable::class, $type->parse('2023-12-29 18:56:01'));
    }

    public static function parsingDataProvider(): array
    {
        return [
            'with datetime instance' => [
                DateTimeType::make('Y-m-d H:i:s'),
                ['2023-12-29 18:56:01', new DateTimeImmutable('2023-12-29 18:56:01')],
                [new \stdClass(), [], true, false, -100, '100']
            ]
        ];
    }
}
