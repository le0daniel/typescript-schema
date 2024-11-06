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
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

final class IntType implements LeafType
{
    /** @uses Nullable<IntType> */
    use Nullable, Coerce, Validators, Refinable, Transformable;

    private function coerceValue(mixed $value): mixed
    {
        try {
            return (int)$value;
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
        return $this->applyCoerceToInputDefinition(
            Definition::same(['type' => 'integer']),
            [
                'oneOf' => [
                    ['type' => 'number'],
                    ['type' => 'string'],
                    ['type' => 'boolean'],
                ]
            ],
        );
    }

    public function min(int $minValue, bool $including = true): IntType
    {
        return $this->addValidator(static function (int $value) use ($minValue, $including): bool {
            if ($including ? $value >= $minValue : $value > $minValue) {
                return true;
            }

            throw ($including
                ? Issue::custom("Value must be bigger than or equal to {$minValue}", ['min' => $minValue], localizationKey: "int.invalid_min.including")
                : Issue::custom("Value must be bigger than {$minValue}", ['min' => $minValue], localizationKey: "int.invalid_min.excluding")
            );
        });
    }

    public function max(int $maxValue, bool $including = true): IntType
    {
        return $this->addValidator(static function (int $value) use ($maxValue, $including): bool {
            if ($including ? $value <= $maxValue : $value < $maxValue) {
                return true;
            }

            throw ($including
                ? Issue::custom("Value must be smaller than or equal to {$maxValue}", ['max' => $maxValue], localizationKey: "int.invalid_max.including")
                : Issue::custom("Value must be smaller than {$maxValue}", ['max' => $maxValue], localizationKey: "int.invalid_max.excluding")
            );
        });
    }

    public function parseAndValidate(mixed $value, Context $context): Value|int
    {
        return $this->validateAndSerialize(
            $this->applyCoercionIfEnabled($value), $context
        );
    }

    public function validateAndSerialize(mixed $value, Context $context): Value|int
    {
        if (!is_int($value)) {
            $context->addIssue(Issue::invalidType('integer', $value));
            return Value::INVALID;
        }

        if (!$this->runValidators($value, $context)) {
            return Value::INVALID;
        }

        return $value;
    }
}
