<?php declare(strict_types=1);

namespace TypescriptSchema\Primitives;

use TypescriptSchema\Exceptions\Issue;

/**
 * @extends PrimitiveType<float>
 */
final class FloatType extends PrimitiveType 
{

    protected function parsePrimitiveType(mixed $value): mixed
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false && filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
            throw Issue::invalidType('float', $value);
        }

        if (is_string($value)) {
            throw Issue::invalidType('float', $value);
        }

        return (float) $value;
    }

    protected function toDefinition(): string
    {
        return 'number';
    }

    protected function coerceValue(mixed $value): mixed
    {
        if (!is_scalar($value)) {
            throw Issue::coercionFailure('float', $value);
        }

        try {
            return (float) $value;
        } catch (\Throwable) {
            throw Issue::coercionFailure('float', $value);
        }
    }
}
