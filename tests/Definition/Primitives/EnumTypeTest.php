<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use TypescriptSchema\Tests\TestCase;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Primitives\EnumType;
use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Tests\Mocks\IntBackedEnumMock;
use TypescriptSchema\Tests\Mocks\StringBackedEnumMock;
use TypescriptSchema\Tests\Mocks\UnitEnumMock;

class EnumTypeTest extends TestCase
{
    public function testEnumDefinitionWhenTransformingToStrings(): void
    {
        $unitEnumSchema = EnumType::make(UnitEnumMock::class);
        self::assertEquals(['enum' => ['SUCCESS', 'FAILURE']], $unitEnumSchema->toDefinition()->output());
        self::assertEquals(['enum' => ['SUCCESS', 'FAILURE']], $unitEnumSchema->toDefinition()->input());

        $stringEnumSchema = EnumType::make(StringBackedEnumMock::class);
        self::assertEquals(['enum' => ['SUCCESS', 'ERROR']], $stringEnumSchema->toDefinition()->output());
        self::assertEquals(['enum' => ['SUCCESS', 'ERROR']], $stringEnumSchema->toDefinition()->input());

        $intEnumSchema = EnumType::make(IntBackedEnumMock::class);
        self::assertEquals(['enum' => ['SUCCESS', 'FAILURE']], $intEnumSchema->toDefinition()->output());
        self::assertEquals(['enum' => ['SUCCESS', 'FAILURE']], $intEnumSchema->toDefinition()->input());
    }

    public function testEnumParsing()
    {
        $type = EnumType::make(UnitEnumMock::class);
        self::assertEquals(UnitEnumMock::FAILURE, $type->parseAndValidate('FAILURE', new Context()));
        self::assertEquals('FAILURE', $type->validateAndSerialize('FAILURE', new Context()));
        self::assertEquals(UnitEnumMock::SUCCESS, $type->parseAndValidate(UnitEnumMock::SUCCESS, new Context()));
        self::assertEquals('SUCCESS', $type->validateAndSerialize('SUCCESS', new Context()));

        self::assertEquals(Value::INVALID,$type->parseAndValidate('', new Context()));
    }

}
