<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Complex;

use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Schema\Definition;
use TypescriptSchema\Definition\Complex\TransformType;
use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Tests\TestCase;

class TransformTypeTest extends TestCase
{

    private function mockedType(): Type
    {
        return new class implements Type {
            public function resolve(mixed $value, Context $context): mixed
            {
                return "String!";
            }

            public function toDefinition(): SchemaDefinition
            {
                return new Definition(['type' => 'number'], ['type' => 'string']);
            }
        };
    }

    public function testResolve()
    {
        $transformer = new TransformType($type = $this->mockedType(), function (): string {
            return "I am the string now!";
        });

        self::assertEquals('String!', $type->resolve(null, new Context()));
        self::assertEquals("I am the string now!", $transformer->resolve(null, new Context()));
    }

    public function testToDefinitionWithoutOverwriting()
    {
        $transformer = new TransformType($this->mockedType(), function (): string {
            return "I am the string now!";
        });

        self::assertEquals(['type' => 'number'], $transformer->toDefinition()->input());
        self::assertEquals([], $transformer->toDefinition()->output());
    }

    public function testToDefinitionWithOverwriting()
    {
        $transformer = new TransformType($this->mockedType(), function (): string {
            return "I am the string now!";
        }, ['type' => 'boolean']);

        self::assertEquals(['type' => 'number'], $transformer->toDefinition()->input());
        self::assertEquals(['type' => 'boolean'], $transformer->toDefinition()->output());
    }

    public function testToDefinitionWithOverwritingClosure()
    {
        $transformer = new TransformType($this->mockedType(), function (): string {
            return "I am the string now!";
        }, fn(array $previous) => ['type' => 'boolean', 'previous' => $previous]);

        self::assertEquals(['type' => 'number'], $transformer->toDefinition()->input());
        self::assertEquals(['type' => 'boolean', 'previous' => ['type' => 'string']], $transformer->toDefinition()->output());
    }
}
