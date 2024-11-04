<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use Throwable;
use TypescriptSchema\Contracts\LeafType;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Shared\Coerce;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Validators;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

final class IntType implements LeafType
{
    /** @uses Nullable<IntType> */
    use Coerce, Nullable, Validators;

    private function coerceValue(mixed $value): mixed
    {
        try {
            return match ($value) {
                'true' => 1,
                'false' => 0,
                default => (int) $value
            };
        } catch (Throwable) {
            return $value;
        }
    }

    public static function make(): self
    {
        return new self();
    }

    public function toDefinition(): SchemaDefinition
    {
        return Definition::same(['type' => 'integer']);
    }

    public function min(int $minValue, bool $including = true): IntType
    {
        return $this->addValidator(static function (int $value) use ($minValue, $including): bool {
            return $including ? $value >= $minValue : $value > $minValue;
        }, 'Value must be greater than or equal to ' . $minValue);
    }

    public function max(int $maxValue, bool $including = true): IntType
    {
        return $this->addValidator(static function (int $value) use ($maxValue, $including): bool {
            return $including ? $value <= $maxValue : $value < $maxValue;
        }, 'Value must be smaller than or equal to ' . $maxValue);
    }

    public function parseAndValidate(mixed $value, Context $context): Value|int
    {
        // Coercion is ONLY applied on input.
        $value = $this->applyCoercionIfEnabled($value);

        if (!is_int($value)) {
            $context->addIssue(Issue::invalidType('integer', $value));
            return Value::INVALID;
        }

        if (!$this->runValidators($value, $context)) {
            return Value::INVALID;
        }
        return $value;
    }

    public function validateAndSerialize(mixed $value, Context $context): Value|int
    {
        if (!is_int($value)) {
            $context->addIssue(Issue::invalidType('integer', $value));
            return Value::INVALID;
        }

        // In case validation is disabled, we only guarantee the type
        // All other validations are disabled. This can be useful in production
        // to reduce the memory consumption.
        if (!$context->validateOnSerialize) {
            return $value;
        }

        if (!$this->runValidators($value, $context)) {
            return Value::INVALID;
        }

        return $value;
    }
}
