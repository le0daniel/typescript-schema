<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use TypescriptSchema\Contracts\ComplexType;
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

final class RecordType implements ComplexType
{
    /** @uses Nullable<RecordType> */
    use Nullable, Refinable, Transformable;

    public function __construct(private readonly Type $ofType)
    {
    }

    public static function make(Type $ofType): self
    {
        return new self($ofType);
    }

    public function toDefinition(): SchemaDefinition
    {
        return new Definition(
            [
                'type' => 'object',
                'additionalProperties' => $this->ofType->toDefinition()->input(),
            ],
            [
                'type' => 'object',
                'additionalProperties' => $this->ofType->toDefinition()->output(),
            ]
        );
    }

    public function resolve(mixed $value, Context $context): mixed
    {
        if (!is_iterable($value)) {
            $context->addIssue(Issue::invalidType('array', $value));
            return Value::INVALID;
        }

        $isDirty = false;
        $values = [];
        foreach ($value as $name => $itemValue) {
            $context->enter($name);

            try {
                if (!is_string($name)) {
                    $context->addIssue(Issue::invalidKey("string", $name));
                    $isDirty = true;
                    continue;
                }

                $value = Executor::execute($this->ofType, $itemValue, $context);
                if ($value === Value::INVALID) {
                    $isDirty = true;
                    continue;
                }
                $values[$name] = $value;
            } finally {
                $context->leave();
            }
        }

        if ($isDirty) {
            return Value::INVALID;
        }

        return $values;
    }
}
