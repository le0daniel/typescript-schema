<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use TypescriptSchema\Contracts\LeafType;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Shared\Coerce;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

final class BoolType implements LeafType
{
    /** @uses Nullable<BoolType> */
    use Nullable, Coerce;

    protected function coerceValue(mixed $value): mixed
    {
        return match ($value) {
            true, 'true', 1, '1' => true,
            false, 'false', 0, '0' => false,
            default => Value::INVALID
        };
    }

    public function toDefinition(): SchemaDefinition
    {
        if ($this->coerce) {
            return new Definition(
                [
                    "oneOf" => [
                        ["type" => "string"],
                        ["type" => "boolean"],
                        ["type" => "number"],
                    ]
                ],
                [
                    'type' => "boolean",
                ]
            );
        }

        return Definition::same([
            'type' => "boolean",
        ]);
    }


    public function parseAndValidate(mixed $value, Context $context): Value|bool
    {
        $value = $this->applyCoercionIfEnabled($value);
        if (is_bool($value)) {
            return $value;
        }

        $context->addIssue(Issue::invalidType('boolean', $value));
        return Value::INVALID;
    }

    public function validateAndSerialize(mixed $value, Context $context): Value|bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $context->addIssue(Issue::invalidType('boolean', $value));
        return Value::INVALID;
    }
}
