<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\SerializesOutputValue;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Schema\Definition;
use TypescriptSchema\Definition\Shared\HasDefaultValue;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Helpers\Context;
use UnitEnum;

final class LiteralType implements Type, SerializesOutputValue
{
    /** @uses Nullable<LiteralType> */
    use Nullable, Refinable, Transformable, HasDefaultValue;

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

    public function parse(mixed $value, Context $context): mixed
    {
        $value = $this->applyDefaultValue($value);
        if ($this->literalValue instanceof UnitEnum && $value === $this->literalValue->name) {
            return $this->literalValue;
        }

        if ($value !== $this->literalValue) {
            return Value::INVALID;
        }

        return $value;
    }

    public function serializeValue(mixed $value, Context $context): mixed
    {
        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        return $value;
    }
}
