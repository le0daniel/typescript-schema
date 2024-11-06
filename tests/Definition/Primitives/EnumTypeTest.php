<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Primitives;

use TypescriptSchema\Data\Enum\ExecutionMode;
use TypescriptSchema\Execution\Executor;
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
        self::assertEquals(UnitEnumMock::FAILURE, $type->resolve('FAILURE', new Context()));
        self::assertEquals(UnitEnumMock::SUCCESS, $type->resolve(UnitEnumMock::SUCCESS, new Context()));

        self::assertEquals('FAILURE', Executor::execute($type,'FAILURE', new Context(mode: ExecutionMode::SERIALIZE)));
        self::assertEquals('SUCCESS', Executor::execute($type, UnitEnumMock::SUCCESS, new Context(mode: ExecutionMode::SERIALIZE)));

        self::assertEquals(Value::INVALID,$type->resolve('', new Context()));
    }

}
