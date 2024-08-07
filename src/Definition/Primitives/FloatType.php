<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use TypescriptSchema\Data\Definition;
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

    public function toDefinition(): Definition
    {
        return Definition::same('number');
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
