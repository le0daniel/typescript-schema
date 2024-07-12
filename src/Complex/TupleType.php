<?php declare(strict_types=1);

namespace TypescriptSchema\Complex;

use TypescriptSchema\BaseType;
use TypescriptSchema\Context\Context;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\IsNullable;
use TypescriptSchema\Type;
use RuntimeException;

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

    public function toDefinition(): string
    {
        $definitions = array_map(fn(Type $type): string => $type->toDefinition(), $this->types);
        return '[' . implode(', ', $definitions) . ']';
    }
}
