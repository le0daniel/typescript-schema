<?php declare(strict_types=1);

namespace TypescriptSchema\Primitives;

use Throwable;
use TypescriptSchema\Exceptions\Issue;

/**
 * @extends PrimitiveType<int>
 */
final class IntType extends PrimitiveType
{
    protected function parsePrimitiveType(mixed $value): int
    {
        if (!is_int($value)) {
            throw Issue::invalidType('int', $value);
        }

        return (int) $value;
    }

    protected function coerceValue(mixed $value): int
    {
        try {
            return (int) $value;
        } catch (Throwable) {
            throw Issue::coercionFailure('int', $value);
        }
    }

    /**
     * @template T
     * @psalm-param T $this
     * @param int $min
     * @return T
     */
    public function min(int $min): static
    {
        return $this->addValidator(static function(int $value) use ($min) {
            if ($value < $min) {
                throw Issue::custom("Expected value to be bigger than {$min}, got {$value}");
            }
            return true;
        });
    }

    public function max(int $max): static {
        return $this->addValidator(static function(int $value) use ($max) {
            if ($value > $max) {
                throw Issue::custom("Expected value to be smaller than {$max}, got {$value}");
            }
            return true;
        });
    }

    protected function toDefinition(): string
    {
        return 'number';
    }
}
