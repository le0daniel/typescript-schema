<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Definition\Wrappers;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Definition\Wrappers\NullableWrapper;
use TypescriptSchema\Definition\Wrappers\TransformWrapper;
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
            TransformWrapper::make(
                NullableWrapper::make(StringType::make()),
                fn() => Typescript::literal('my val'),
            )
        );
    }

    public function testResolvesCorrectlyWhenTypeHasDefaultValue()
    {
        $schema = NullableWrapper::make(StringType::make()->default('test value'));
        self::assertEquals('test value', $schema->parse(null));
    }

    public function testErrorBoundary(): void
    {
        $schema = NullableWrapper::make(StringType::make()->min(5));
        $result = $schema->safeParse('one', true);
        self::assertFalse($result->isSuccess());
        self::assertTrue($result->isPartial());
        self::assertFalse($result->isFailure());
        self::assertEquals(null, $result->getData());

        $this->expectException(ParsingException::class);
        $schema->parse('one');
    }

    public function testDefinition()
    {
        self::assertEquals('string|null', StringType::make()->nullable()->toDefinition()->output);
        self::assertEquals('string|null', StringType::make()->nullable()->toDefinition()->input);
    }

    public function testProxyFunctionality(): void
    {
        $mockType = new class implements Type
        {

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

            public function toDefinition(): Definition
            {
                return Definition::same('something');
            }
        };

        $nullable = NullableWrapper::make($mockType);
        self::assertEquals('this is a string', $nullable->testProxy());
        self::assertEquals('something|null', $nullable->toDefinition()->input);
        self::assertEquals('something|null', $nullable->toDefinition()->output);

        $changedType = $nullable->changeToString();
        self::assertNotSame($changedType, $nullable);
        self::assertInstanceOf(NullableWrapper::class, $changedType);

        self::assertEquals('string|null', $changedType->toDefinition()->input);
        self::assertEquals('string|null', $changedType->toDefinition()->output);
        self::assertEquals('something|null', $nullable->toDefinition()->input);
        self::assertEquals('something|null', $nullable->toDefinition()->output);

        self::assertInstanceOf(StringType::class, $changedType->unwrap());
    }

}
