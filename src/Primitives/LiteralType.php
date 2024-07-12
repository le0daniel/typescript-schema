<?php declare(strict_types=1);

namespace TypescriptSchema\Primitives;

use RuntimeException;
use TypescriptSchema\Exceptions\Issue;
use UnitEnum;

/**
 * @extends PrimitiveType<string|int|bool|UnitEnum>
 */
final class LiteralType extends PrimitiveType 
{
    public function __construct(private string|int|bool|UnitEnum|null $literalValue)
    {
    }

    public static function make(string|int|bool|UnitEnum|null $literalValue = null): static
    {
        return new self($literalValue);
    }

    public function value(string|int|bool|UnitEnum $literalValue): static
    {
        $instance = clone $this;
        $instance->literalValue = $literalValue;
        return $instance;
    }

    /**
     * Ensures that enums are of type string after validation is successful.
     * This is useful for serialization, as you can not JSON serialize UnitEnums.
     * @return self
     */
    public function enumsAsString(): static
    {
        return $this->addTransformer(function (mixed $value) {
            if ($value instanceof UnitEnum) {
                return $value->name;
            }
            return $value;
        });
    }

    protected function parsePrimitiveType(mixed $value): mixed
    {
        if ($this->literalValue === null) {
            throw new RuntimeException('Literal value cannot be null.');
        }

        // In case the Enum is matched by name, we return the Enum.
        // ToDo: Handle backed enum
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

    public function toDefinition(): string
    {
        return match (true) {
            is_string($this->literalValue) => "'{$this->literalValue}'",
            is_int($this->literalValue) => "{$this->literalValue}",
            is_bool($this->literalValue) => "{$this->boolAsString()}",
            $this->literalValue instanceof UnitEnum => "'{$this->literalValue->name}'",
        };
    }

    private function boolAsString(): string
    {
        return $this->literalValue ? 'true' : 'false';
    }
}
