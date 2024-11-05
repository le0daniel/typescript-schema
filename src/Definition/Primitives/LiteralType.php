<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use TypescriptSchema\Contracts\LeafType;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Helpers\Context;
use UnitEnum;

final class LiteralType implements LeafType
{
    /** @uses Nullable<LiteralType> */
    use Nullable;

    public function __construct(private readonly string|int|float|bool|UnitEnum $literalValue)
    {
    }

    public static function make(string|int|float|bool|UnitEnum $literalValue): static
    {
        return new self($literalValue);
    }

    public function toDefinition(): SchemaDefinition
    {
        $definition = $this->literalValue instanceof UnitEnum
            ? $this->literalValue->name
            : $this->literalValue;

        return Definition::same([
            'const' => $definition
        ]);
    }

    public function parseAndValidate(mixed $value, Context $context): mixed
    {
        if ($this->literalValue instanceof UnitEnum && $value === $this->literalValue->name) {
            return $this->literalValue;
        }

        if ($value !== $this->literalValue) {
            return Value::INVALID;
        }

        return $value;
    }

    public function validateAndSerialize(mixed $value, Context $context): mixed
    {
        // Ensure enums are serialized as strings
        if ($this->literalValue instanceof UnitEnum && $value === $this->literalValue->name) {
            return $this->literalValue->name;
        }

        if ($value !== $this->literalValue) {
            return Value::INVALID;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        return $value;
    }
}
