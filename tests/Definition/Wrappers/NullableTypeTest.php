<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Wrappers;

use TypescriptSchema\Tests\TestCase;
use RuntimeException;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Definition\Wrappers\NullableWrapper;
use TypescriptSchema\Exceptions\ParsingException;
use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Utils\Typescript;

class NullableTypeTest extends TestCase
{
    public function testNotWrappableInsideItself()
    {
        $this->expectException(RuntimeException::class);
        NullableWrapper::make(NullableWrapper::make(StringType::make()));
    }

    public function testNotWrappableInsideItselfWithOtherWrappedTypeBetween()
    {


        $this->expectException(RuntimeException::class);
        NullableWrapper::make(
            NullableWrapper::make(StringType::make()),
        );
    }

    public function testErrorBoundary(): void
    {
        $type = NullableWrapper::make(StringType::make()->minLength(5));
        self::assertSuccess(
            $type->resolve('one-one', new Context())
        );

        self::assertFailure(
            $type->resolve('one', new Context())
        );

        self::assertSuccess(
            $type->resolve('one', $context = new Context(allowPartialFailures: true))
        );
        self::assertCount(1, $context->getIssues());
    }

    public function testDefinition()
    {
        self::assertEquals('string|null', Typescript::fromJsonSchema(StringType::make()->nullable()->toDefinition()->toOutputSchema()));
        self::assertEquals('string|null', Typescript::fromJsonSchema(StringType::make()->nullable()->toDefinition()->toInputSchema()));
    }

    public function testProxyFunctionality(): void
    {
        $mockType = new class implements Type {

            public function testProxy(): string
            {
                return 'this is a string';
            }

            public function changeToString(): Type
            {
                return StringType::make();
            }

            public function execute(mixed $value, Context $context): mixed
            {
                return ['value'];
            }

            public function toDefinition(): SchemaDefinition
            {
                return Definition::same(['type' => 'boolean']);
            }
        };

        $nullable = NullableWrapper::make($mockType);
        self::assertEquals('this is a string', $nullable->testProxy());

        $changedType = $nullable->changeToString();
        self::assertNotSame($changedType, $nullable);
        self::assertInstanceOf(NullableWrapper::class, $changedType);

        self::assertEquals('string|null', Typescript::fromJsonSchema($changedType->toDefinition()->toInputSchema()));
        self::assertEquals('string|null', Typescript::fromJsonSchema($changedType->toDefinition()->toOutputSchema()));
        self::assertEquals('boolean|null', Typescript::fromJsonSchema($nullable->toDefinition()->toInputSchema()));
        self::assertEquals('boolean|null', Typescript::fromJsonSchema($nullable->toDefinition()->toOutputSchema()));

        self::assertInstanceOf(StringType::class, $changedType->unwrap());
    }

}
