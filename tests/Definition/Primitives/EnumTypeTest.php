<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\EnumType;
use TypescriptSchema\Tests\Mocks\IntBackedEnumMock;
use TypescriptSchema\Tests\Mocks\StringBackedEnumMock;
use TypescriptSchema\Tests\Mocks\UnitEnumMock;

class EnumTypeTest extends TestCase
{
    public function testEnumDefinition(): void
    {
        $unitEnumSchema = EnumType::make(UnitEnumMock::class);
        self::assertEquals('never', $unitEnumSchema->toOutputDefinition());
        self::assertEquals("'SUCCESS'|'FAILURE'", $unitEnumSchema->toInputDefinition());

        $stringEnumSchema = EnumType::make(StringBackedEnumMock::class);
        self::assertEquals("'success'|'error'", $stringEnumSchema->toOutputDefinition());
        self::assertEquals("'SUCCESS'|'ERROR'", $stringEnumSchema->toInputDefinition());

        $intEnumSchema = EnumType::make(IntBackedEnumMock::class);
        self::assertEquals("0|1", $intEnumSchema->toOutputDefinition());
        self::assertEquals("'SUCCESS'|'FAILURE'", $intEnumSchema->toInputDefinition());
    }

    public function testEnumDefinitionWhenTransformingToStrings(): void
    {
        $unitEnumSchema = EnumType::make(UnitEnumMock::class)->asString();
        self::assertEquals("'SUCCESS'|'FAILURE'", $unitEnumSchema->toOutputDefinition());
        self::assertEquals("'SUCCESS'|'FAILURE'", $unitEnumSchema->toInputDefinition());

        $stringEnumSchema = EnumType::make(StringBackedEnumMock::class)->asString();
        self::assertEquals("'SUCCESS'|'ERROR'", $stringEnumSchema->toOutputDefinition());
        self::assertEquals("'SUCCESS'|'ERROR'", $stringEnumSchema->toInputDefinition());

        $intEnumSchema = EnumType::make(IntBackedEnumMock::class)->asString();
        self::assertEquals("'SUCCESS'|'FAILURE'", $intEnumSchema->toOutputDefinition());
        self::assertEquals("'SUCCESS'|'FAILURE'", $intEnumSchema->toInputDefinition());
    }


    public function testSuccessfulParse()
    {
        self::assertEquals(
            UnitEnumMock::FAILURE,
            EnumType::make(UnitEnumMock::class)->parse(UnitEnumMock::FAILURE)
        );

        self::assertEquals(
            UnitEnumMock::FAILURE,
            EnumType::make(UnitEnumMock::class)->parse('FAILURE')
        );

        self::assertEquals(
            StringBackedEnumMock::ERROR,
            EnumType::make(StringBackedEnumMock::class)->parse(StringBackedEnumMock::ERROR)
        );

        self::assertEquals(
            StringBackedEnumMock::ERROR,
            EnumType::make(StringBackedEnumMock::class)->parse('ERROR')
        );

        self::assertEquals(
            IntBackedEnumMock::SUCCESS,
            EnumType::make(IntBackedEnumMock::class)->parse(IntBackedEnumMock::SUCCESS)
        );

        self::assertEquals(
            IntBackedEnumMock::SUCCESS,
            EnumType::make(IntBackedEnumMock::class)->parse('SUCCESS')
        );
    }

    public function testCoercionOfValues(): void
    {
        self::assertEquals(
            StringBackedEnumMock::ERROR,
            EnumType::make(StringBackedEnumMock::class)->coerce()->parse('error')
        );

        self::assertFalse(
            EnumType::make(StringBackedEnumMock::class)->safeParse('error')->isSuccess()
        );

        self::assertEquals(
            IntBackedEnumMock::SUCCESS,
            EnumType::make(IntBackedEnumMock::class)->coerce()->parse(0)
        );

        self::assertFalse(
            EnumType::make(IntBackedEnumMock::class)->safeParse(0)->isSuccess()
        );
    }

    public function testAsStringParsing(): void
    {
        self::assertEquals(
            'FAILURE',
            EnumType::make(UnitEnumMock::class)->asString()->parse(UnitEnumMock::FAILURE)
        );
        self::assertEquals(
            'ERROR',
            EnumType::make(StringBackedEnumMock::class)->asString()->parse(StringBackedEnumMock::ERROR)
        );
        self::assertEquals(
            'SUCCESS',
            EnumType::make(IntBackedEnumMock::class)->asString()->parse(IntBackedEnumMock::SUCCESS)
        );
    }
}
