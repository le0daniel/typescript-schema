<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use Throwable;
use TypescriptSchema\Contracts\LeafType;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Shared\Coerce;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Validators;
use TypescriptSchema\Helpers\Context;

final class NumberType implements LeafType
{
    /** @uses Nullable<NumberType> */
    use Coerce, Nullable, Validators;

    public function toDefinition(): SchemaDefinition
    {
        return Definition::same([
            'type' => 'number'
        ]);
    }

    protected function coerceValue(mixed $value): mixed
    {
        if (!is_scalar($value)) {
            return $value;
        }

        try {
            return (float) $value;
        } catch (Throwable) {
            return $value;
        }
    }

    public function parseAndValidate(mixed $value, Context $context): mixed
    {
        $value = $this->applyCoercionIfEnabled($value);
        if (filter_var($value, FILTER_VALIDATE_INT) === false && filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
            return Value::INVALID;
        }

        if (!$this->runValidators($value, $context)) {
            return Value::INVALID;
        }

        return (float) $value;
    }

    public function validateAndSerialize(mixed $value, Context $context): Value|float
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false && filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
            return Value::INVALID;
        }

        return (float) $value;
    }
}
