<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Schema\Definition;
use TypescriptSchema\Definition\Shared\Coerce;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

final class BoolType implements Type
{
    /** @uses Nullable<BoolType> */
    use Nullable, Coerce, Refinable, Transformable;

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
        return $this->applyCoerceToInputDefinition(
            Definition::same(['type' => "boolean",]),
            [
                "oneOf" => [
                    ["type" => "string"],
                    ["type" => "boolean"],
                    ["type" => "number"],
                ]
            ]);
    }


    public function resolve(mixed $value, Context $context): Value|bool
    {
        $value = $this->applyCoercionIfEnabled($value);

        if (is_bool($value)) {
            return $value;
        }

        $context->addIssue(Issue::invalidType('boolean', $value));
        return Value::INVALID;
    }
}
