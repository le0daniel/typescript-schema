<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;


use Generator;
use TypescriptSchema\Contracts\ComplexType;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Schema\WrappedDefinition;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\Context;

final class ArrayType implements ComplexType
{
    use Nullable, Refinable, Transformable;

    public function __construct(
        private readonly Type $type,
    )
    {
    }

    public static function make(Type $type): self
    {
        return new self($type);
    }

    public function toDefinition(): SchemaDefinition
    {
        return WrappedDefinition::same(
            $this->type->toDefinition(), fn(array $def) => [
                'type' => 'array',
                'items' => $this->type->toDefinition()->input()
            ]
        );
    }

    public function resolve(mixed $value, Context $context): mixed
    {
        // We accept more types when serializing
        if (!is_iterable($value) && !$value instanceof Generator) {
            $context->addIssue(Issue::invalidType('iterable', $value));
            return Value::INVALID;
        }

        $parsed = [];
        $index = 0;
        foreach ($value as $item) {
            $context->enter($index);
            $index++;

            try {
                $itemValue = Executor::execute($this->type, $item, $context);
                if ($itemValue === Value::INVALID) {
                    return Value::INVALID;
                }

                $parsed[] = $itemValue;
            } finally {
                $context->leave();
            }
        }

        return $parsed;
    }
}
