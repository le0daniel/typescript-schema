<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use Throwable;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Schema\Definition;
use TypescriptSchema\Definition\Shared\BaseType;
use TypescriptSchema\Definition\Shared\Coerce;
use TypescriptSchema\Definition\Shared\HasDefaultValue;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Definition\Shared\Validators;
use TypescriptSchema\Helpers\Context;

final class NumberType implements Type
{
    /** @use Nullable<NumberType> */
    use Nullable, Coerce, Validators, Refinable, Transformable, HasDefaultValue, BaseType;

    public static function make(): NumberType
    {
        return new NumberType();
    }

    public function toDefinition(): SchemaDefinition
    {
        return $this->applyCoerceToInputDefinition(
            Definition::same(['type' => 'number']),
            [
                'oneOf' => [
                    ['type' => 'number'],
                    ['type' => 'string'],
                    ['type' => 'boolean'],
                ],
            ],
        );
    }

    protected function coerceValue(mixed $value): mixed
    {
        if (!is_scalar($value)) {
            return $value;
        }

        if (filter_var($value, FILTER_VALIDATE_FLOAT) !== false || filter_var($value, FILTER_VALIDATE_INT) !== false) {
            return (float) $value;
        }

        if (is_bool($value)) {
            return (float) ($value ? 1 : 0);
        }

        return (float) $value;
    }

    public function parse(mixed $value, Context $context): Value|int|float
    {
        $value = $this->applyCoercionIfEnabled(
            $this->applyDefaultValue($value)
        );

        if (filter_var($value, FILTER_VALIDATE_INT) === false && filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
            return Value::INVALID;
        }

        $value = (float) $value;

        if (!$this->runValidators($value, $context)) {
            return Value::INVALID;
        }

        return $value;
    }
}
