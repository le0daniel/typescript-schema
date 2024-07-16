<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use RuntimeException;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\BaseType;
use TypescriptSchema\Definition\Shared\IsNullable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

final class TupleType extends BaseType
{
    use IsNullable;

    /**
     * @param array<Type> $types
     */
    protected function __construct(
        protected readonly array $types,
    )
    {
        if (empty($this->types)) {
            throw new RuntimeException("A tuple can not be empty");
        }
    }

    public static function make(Type ... $types): self
    {
        return new self($types);
    }

    protected function validateAndParseType(mixed $value, Context $context): array|Value
    {
        if (!is_array($value)) {
            throw Issue::invalidType('array', $value);
        }

        if (!array_is_list($value)) {
            throw Issue::custom("Expected list array, got non list array, check the array keys.");
        }

        if (count($this->types) !== count($value)) {
            throw Issue::custom("Amount of values did not match expected tuple values.");
        }

        $parsed = [];
        foreach ($value as $index => $itemValue) {
            $context->enter($index);
            try{
                 $value = $this->types[$index]->execute($itemValue, $context);
                 if ($value === Value::INVALID) {
                     return Value::INVALID;
                 }
                $parsed[] = $value;
            } finally {
                $context->leave();
            }
        }
        return $parsed;
    }

    protected function toDefinition(): string
    {
        $definitions = array_map(fn(Type $type): string => $type->toDefinition(), $this->types);
        return '[' . implode(', ', $definitions) . ']';
    }
}
