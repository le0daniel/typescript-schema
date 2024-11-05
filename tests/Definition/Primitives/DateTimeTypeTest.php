<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use DateTimeImmutable;
use TypescriptSchema\Tests\TestCase;
use stdClass;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Primitives\DateTimeType;
use TypescriptSchema\Helpers\Context;

class DateTimeTypeTest extends TestCase
{
    public function testsReturnsInstanceOfDateTimeImmutable(): void
    {
        $type = DateTimeType::make('Y-m-d H:i:s');
        self::assertInstanceOf(DateTimeImmutable::class, $type->parseAndValidate('2023-12-29 18:56:01', new Context()));
        self::assertSame('2023-12-29 18:56:01', $type->parseAndValidate('2023-12-29 18:56:01', new Context())->format('Y-m-d H:i:s'));
    }

    public function testSerialize()
    {
        self::assertSame(
            '2023-12-29 18:56:01',
            DateTimeType::make('Y-m-d H:i:s')->validateAndSerialize('2023-12-29 18:56:01', new Context())
        );

        self::assertSame(
            Value::INVALID,
            DateTimeType::make()->validateAndSerialize('2023-12-29 18:56:01', new Context())
        );
    }

    public function testBefore()
    {
        $type = DateTimeType::make('Y-m-d H:i:s')->before(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-29 18:56:01'));

        self::assertSame(Value::INVALID,
            $type->parseAndValidate('2023-12-29 18:56:01', new Context()),
        );

        self::assertInstanceOf(DateTimeImmutable::class, $type->parseAndValidate('2023-12-29 18:56:00', new Context()));
    }

    public function testAfter()
    {
        $type = DateTimeType::make('Y-m-d H:i:s')->after(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2023-12-29 18:56:01'));

        self::assertSame(Value::INVALID,
            $type->parseAndValidate('2023-12-29 18:56:01', new Context()),
        );

        self::assertInstanceOf(DateTimeImmutable::class, $type->parseAndValidate('2023-12-29 18:56:02', new Context()));
    }

    public static function parsingDataProvider(): array
    {
        return [
            'with datetime instance' => [
                DateTimeType::make('Y-m-d H:i:s'),
                ['2023-12-29 18:56:01', new DateTimeImmutable('2023-12-29 18:56:01')],
                [new stdClass(), [], true, false, -100, '100']
            ],
            'with before defined' => [
                DateTimeType::make('Y-m-d H:i:s')->before(new DateTimeImmutable('2023-12-29 18:56:01')),
                ['2023-12-29 18:56:00'],
                ['2023-12-29 18:56:1']
            ],
            'with after defined' => [
                DateTimeType::make('Y-m-d H:i:s')->after(new DateTimeImmutable('2023-12-29 18:56:01')),
                ['2023-12-29 18:56:02'],
                ['2023-12-29 18:56:01']
            ],
        ];
    }
}
