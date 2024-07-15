<?php declare(strict_types=1);

namespace Tests\Complex;

use RuntimeException;
use TypescriptSchema\Complex\NullableType;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Exceptions\ParsingException;
use TypescriptSchema\Primitives\StringType;
use TypescriptSchema\TransformWrapper;
use TypescriptSchema\Utils\Typescript;

class NullableTypeTest extends TestCase
{

    public function testNotWrappableInsideItself()
    {
        $this->expectException(RuntimeException::class);
        NullableType::make(NullableType::make(StringType::make()));
    }

    public function testNotWrappableInsideItselfWithOtherWrappedTypeBetween()
    {
        $this->expectException(RuntimeException::class);
        NullableType::make(
            TransformWrapper::make(
                NullableType::make(StringType::make()),
                fn() => Typescript::wrapInSingleQuote('my val'),
            )
        );
    }

    public function testResolvesCorrectlyWhenTypeHasDefaultValue()
    {
        $schema = NullableType::make(StringType::make()->default('test value'));
        self::assertEquals('test value', $schema->parse(null));
    }

    public function testErrorBoundary(): void
    {
        $schema = NullableType::make(StringType::make()->min(5));
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
        self::assertEquals('string|null', StringType::make()->nullable()->toOutputDefinition());
        self::assertEquals('string|null', StringType::make()->nullable()->toInputDefinition());
    }

}
