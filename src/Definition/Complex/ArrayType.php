<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;


use TypescriptSchema\Contracts\ComplexType;
use TypescriptSchema\Contracts\OptionallyNamed;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Schema\WrappedDefinition;
use TypescriptSchema\Definition\Shared\BaseType;
use TypescriptSchema\Definition\Shared\Nameable;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\Context;

/**
 *
 */
final class ArrayType implements Type, ComplexType, OptionallyNamed
{
    /** @use Nullable<ArrayType> */
    use Nullable, Refinable, Transformable, BaseType, Nameable;

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

    public function parse(mixed $value, Context $context): mixed
    {
        if (!is_iterable($value)) {
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

    public function getTypes(): array
    {
        return [$this->type];
    }
}
