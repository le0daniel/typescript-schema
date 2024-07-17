<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use BackedEnum;
use RuntimeException;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Utils\Typescript;
use UnitEnum;

/**
 * @extends PrimitiveType<string|int|bool|UnitEnum>
 */
final class LiteralType extends PrimitiveType 
{
    public function __construct(private readonly string|int|float|bool|UnitEnum|null $literalValue)
    {
    }

    public static function make(string|int|float|bool|UnitEnum|null $literalValue = null): static
    {
        return new self($literalValue);
    }

    /**
     * Ensures that enums are of type string after validation is successful.
     * This is useful for serialization, as you can not JSON serialize UnitEnums that are not backed.
     * @return self
     */
    public function enumAsNameString(): static
    {
        return $this->addInternalTransformer(static function (mixed $value) {
            if ($value instanceof UnitEnum) {
                return $value->name;
            }

            return $value;
        }, $this->literalValue instanceof UnitEnum ? Typescript::enumString($this->literalValue) : null);
    }

    protected function parsePrimitiveType(mixed $value): mixed
    {
        if ($this->literalValue === null) {
            throw new RuntimeException('Literal value cannot be null.');
        }

        if ($this->literalValue instanceof UnitEnum && $value === $this->literalValue->name) {
            return $this->literalValue;
        }

        if ($value !== $this->literalValue) {
            throw Issue::invalidType($this->literalValue, $value);
        }

        return $value;
    }

    protected function coerceValue(mixed $value): mixed
    {
        return $value;
    }

    protected function toDefinition(): Definition
    {
        if ($this->literalValue instanceof UnitEnum) {
            return new Definition(
                Typescript::enumString($this->literalValue),
                Typescript::enumValueString($this->literalValue)
            );

        }

        return Definition::same(Typescript::literal($this->literalValue));
    }
}
