<?php declare(strict_types=1);

namespace TypescriptSchema\Primitives;

use TypescriptSchema\Exceptions\Issue;

/**
 * @extends PrimitiveType<bool>
 */
final class BoolType extends PrimitiveType
{

    protected function parsePrimitiveType(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value;
        }

        throw Issue::invalidType('bool', $value);
    }

    protected function coerceValue(mixed $value): bool
    {
        return match ($value) {
            true, 'true', 1, '1' => true,
            false, 'false', 0, '0' => false,
            default => throw Issue::coercionFailure('bool', $value),
        };
    }

    protected function toDefinition(): string
    {
        return 'bool';
    }


}
