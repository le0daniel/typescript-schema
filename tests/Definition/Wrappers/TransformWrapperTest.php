<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Wrappers;

use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Definition\Wrappers\TransformWrapper;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Utils\Typescript;

class TransformWrapperTest extends TestCase
{

    public function testExecute()
    {
        $type = TransformWrapper::make(
            StringType::make(),
            fn() => 'wow',
        );

        $result = $type->safeParse('this is a string');
        self::assertTrue($result->isSuccess());
        self::assertEquals('wow', $result->getData());
    }

    public function testNoCallOnFailure()
    {
        $type = TransformWrapper::make(
            StringType::make(),
            fn() => $this->fail('Should not be called'),
        );

        $result = $type->safeParse(1);
        self::assertTrue($result->isFailure());
        self::assertNull($result->getData());
    }

    public function testToInputDefinition()
    {
        $type = TransformWrapper::make(
            StringType::make(),
            fn() => 'wow',
        );

        self::assertEquals('string', $type->toDefinition()->input);
    }

    public function testToOutputDefinitionWithoutValue()
    {
        $type = TransformWrapper::make(
            StringType::make(),
            fn() => 'wow',
        );

        self::assertEquals('unknown', $type->toDefinition()->output);
    }

    public function testToOutputDefinitionWithStringDefinition()
    {
        $type = TransformWrapper::make(
            StringType::make(),
            fn() => 'wow',
            Typescript::literal('wow')
        );

        self::assertEquals("'wow'", $type->toDefinition()->output);
    }

    public function testToOutputDefinitionWithClosureDefinition()
    {
        $type = TransformWrapper::make(
            StringType::make(),
            fn() => 'wow',
            fn(Definition $previous) => "Array<{$previous->output}|null>"
        );

        self::assertEquals("Array<string|null>", $type->toDefinition()->output);
    }
}
