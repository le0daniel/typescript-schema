<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use Closure;
use TypescriptSchema\Contracts\ComplexType;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\Context;

final class ObjectType implements ComplexType
{
    /** @uses Nullable<ObjectType> */
    use Nullable, Refinable, Transformable;

    private bool|Closure $passThrough = false;

    /**
     * @var array<Field>
     */
    private array $fields;

    /**
     * @param array|Closure $definition
     */
    public function __construct(private readonly array|Closure $definition)
    {
    }

    public static function make(array|Closure $definition): ObjectType
    {
        return new self($definition);
    }

    /**
     * Pass through additional data that is not declared as fields.
     * Those will have the type: [key: string]: unknown
     *
     * If you pass a Closure, you can customize the logic of how pass through works.
     *
     * @param Closure(mixed): array|null $closure
     * @api
     */
    public function passThrough(?Closure $closure = null): ObjectType
    {
        $instance = clone $this;
        $instance->passThrough = $closure ?? true;
        return $instance;
    }

    /**
     * @return array<string, Field>
     */
    protected function fields(): array
    {
        return $this->fields ??= array_map(
            fn(Type|Field $field): Field => $field instanceof Field ? $field : Field::ofType($field),
            $this->definition instanceof Closure ? ($this->definition)() : $this->definition
        );
    }

    /**
     * This is used internally to locate a field by its name.
     * @internal
     */
    public function getFieldByName(string $name): Field
    {
        return $this->fields()[$name];
    }

    public function toDefinition(): SchemaDefinition
    {
        $required = [];

        foreach ($this->fields() as $name => $field) {
            if (!$field->isOptional()) {
                $required[] = $name;
            }
        }

        return new Definition(
            [
                'type' => 'object',
                'properties' => array_map(static fn(Field $field) => ([
                    ...$field->getType()->toDefinition()->toInputSchema(),
                    'description' => $field->getDescription(),
                    'deprecated' => $field->isDeprecated()
                ]), $this->fields),
                'additionalProperties' => !!$this->passThrough,
                'required' => $required,
            ],
            [
                'type' => 'object',
                'properties' => array_map(static fn(Field $field) => ([
                    ...$field->getType()->toDefinition()->toOutputSchema(),
                    'description' => $field->getDescription(),
                    'deprecated' => $field->isDeprecated()
                ]), $this->fields),
                'additionalProperties' => !!$this->passThrough,
                'required' => $required,
            ]
        );
    }

    public function resolve(mixed $value, Context $context): mixed
    {
        $parsed = [];
        $isDirty = false;
        foreach ($this->fields() as $name => $field) {
            $context->enter($name);
            try {
                $fieldValue = $field->resolveValue($name, $value);

                if ($field->isOptional() && $fieldValue === Value::UNDEFINED) {
                    continue;
                }

                $parsedValue = Executor::execute($field->getType(), Value::undefinedToNull($fieldValue), $context);
                if ($parsedValue === Value::INVALID) {
                    $isDirty = true;
                    continue;
                }

                $parsed[$name] = $parsedValue;
            } finally {
                $context->leave();
            }
        }

        if ($isDirty) {
            return Value::INVALID;
        }

        if (!$this->passThrough) {
            return $parsed;
        }

        $passthroughs = match (true) {
            $this->passThrough instanceof Closure => ($this->passThrough)($value),
            is_array($value) => $value,
            default => []
        };

        return [
            ... $passthroughs,
            ... $parsed
        ];
    }
}
