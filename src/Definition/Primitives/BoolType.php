<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use TypescriptSchema\Data\Definition;
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

    public function toDefinition(): Definition
    {
        return Definition::same('any');
    }


}
