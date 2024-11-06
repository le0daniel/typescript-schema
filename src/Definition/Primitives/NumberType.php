<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use Throwable;
use TypescriptSchema\Contracts\LeafType;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Schema\Definition;
use TypescriptSchema\Definition\Shared\Coerce;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Definition\Shared\Validators;
use TypescriptSchema\Helpers\Context;

final class NumberType implements LeafType
{
    /** @uses Nullable<NumberType> */
    use Nullable, Coerce, Validators, Refinable, Transformable;

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

        try {
            return (float) $value;
        } catch (Throwable) {
            return $value;
        }
    }

    public function parseAndValidate(mixed $value, Context $context): mixed
    {
        return $this->validateAndSerialize(
            $this->applyCoercionIfEnabled($value),
            $context,
        );
    }

    public function validateAndSerialize(mixed $value, Context $context): Value|float
    {
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
