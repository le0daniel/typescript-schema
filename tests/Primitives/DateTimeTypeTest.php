<?php declare(strict_types=1);

namespace Tests\Primitives;

use DateTimeImmutable;
use TypescriptSchema\Primitives\DateTimeType;
use PHPUnit\Framework\TestCase;

class DateTimeTypeTest extends TestCase
{

    public function testDefinition()
    {
        self::assertEquals('string', DateTimeType::make()->toInputDefinition());
        self::assertEquals('{date: string, timezone_type: number, timezone: string}', DateTimeType::make()->toOutputDefinition());
        self::assertEquals('string', DateTimeType::make()->toFormattedString()->toOutputDefinition());
        self::assertEquals('string|null', DateTimeType::make()->toFormattedString()->nullable()->toOutputDefinition());
    }

    public function testToStringTransformation(): void
    {
        $type = DateTimeType::make('Y-m-d H:i:s');
        self::assertTrue($type->safeParse(DateTimeImmutable::createFromFormat('Y', '2024'))->isSuccess());
        self::assertTrue($type->safeParse('2023-12-29 18:56:01')->isSuccess());
        self::assertInstanceOf(DateTimeImmutable::class, $type->parse('2023-12-29 18:56:01'));
        self::assertEquals('2023-12-29 18:56:01', $type->toFormattedString()->parse('2023-12-29 18:56:01'));
    }

}
