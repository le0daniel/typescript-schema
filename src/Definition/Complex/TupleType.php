<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use RuntimeException;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Schema\Definition;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\Context;

final class TupleType implements Type
{
    /** @use Nullable<TupleType> */
    use Nullable, Refinable, Transformable;

    /**
     * @param array<Type> $types
     */
    public function __construct(
        protected readonly array $types,
    )
    {
        if (empty($this->types)) {
            throw new RuntimeException("A tuple can not be empty");
        }
    }

    public static function make(Type ...$types): self
    {
        return new self($types);
    }

    public function toDefinition(): SchemaDefinition
    {
        $inputDef = array_map(fn(Type $type): array => $type->toDefinition()->output(), $this->types);
        $outputDef = array_map(fn(Type $type): array => $type->toDefinition()->input(), $this->types);

        return new Definition(
            ['type' => 'array', 'prefixItems' => $inputDef, 'items' => false],
            ['type' => 'array', 'prefixItems' => $outputDef, 'items' => false],
        );
    }

    /**
     * @param mixed $value
     * @param Context $context
     * @return array<mixed>|Value
     */
    private function verifyValue(mixed $value, Context $context): array|Value
    {
        if (!is_array($value)) {
            $context->addIssue(Issue::invalidType('array', $value));
            return Value::INVALID;
        }

        if (!array_is_list($value)) {
            $context->addIssue(Issue::custom("Expected list array, got non list array, check the array keys."));
            return Value::INVALID;
        }

        if (count($this->types) !== count($value)) {
            $context->addIssue(Issue::custom("Amount of values did not match expected tuple values."));
            return Value::INVALID;
        }

        return $value;
    }

    public function parse(mixed $value, Context $context): mixed
    {
        $value = $this->verifyValue($value, $context);
        if ($value === Value::INVALID) {
            return Value::INVALID;
        }
        /** @var array<mixed> $value */
        $isDirty = false;
        $parsed = [];
        foreach ($value as $index => $itemValue) {
            $context->enter($index);
            try {
                $value = Executor::execute($this->types[$index], $itemValue, $context);
                if ($value === Value::INVALID) {
                    $isDirty = true;
                    continue;
                }
                $parsed[] = $value;
            } finally {
                $context->leave();
            }
        }

        if ($isDirty) {
            return Value::INVALID;
        }

        return $parsed;
    }
}
