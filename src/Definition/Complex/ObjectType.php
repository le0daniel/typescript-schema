<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use Closure;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Schema\Definition;
use TypescriptSchema\Definition\Shared\BaseType;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Definition\Shared\Validators;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\Context;

final class ObjectType implements Type
{
    /** @use Nullable<ObjectType> */
    use Nullable, Refinable, Transformable, Validators, BaseType;

    private bool|Closure $passThrough = false;

    /**
     * @var array<Field>
     */
    private array $fields;

    /**
     * @param array<string,Type>|Closure(): array<string, Type> $definition
     */
    public function __construct(private array|Closure $definition)
    {
    }

    public function extend(array $fields): ObjectType
    {
        $clone = clone $this;
        $clone->definition = [
            ... $this->fields(),
            ... $fields,
        ];
        return $clone;
    }

    public function removeFields(array $fields): ObjectType
    {
        $clone = clone $this;
        $clone->definition = array_filter($this->fields(), static function (string $fieldName) use ($fields): bool {
            return !in_array($fieldName, $fields, true);
        }, ARRAY_FILTER_USE_KEY);
        return $clone;
    }

    public function isEmpty(): bool
    {
        return empty($this->fields());
    }

    /**
     * @param array<string,Type>|Closure(): array<string, Type> $definition
     */
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
     * @param Closure(mixed): array<mixed>|null $closure
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
        return $this->fields ??= $this->initFields();
    }

    public function notEmpty(): self
    {
        return $this->addValidator(function (array $object): bool {
            return !empty($object);
        });
    }

    private function initFields(): array
    {
        $fields = [];

        $definition = $this->definition instanceof Closure ? ($this->definition)() : $this->definition;

        /**
         * @var string $name
         * @var Field|Type $fieldDeclaration
         */
        foreach ($definition as $name => $fieldDeclaration) {
            $field = $fieldDeclaration instanceof Field
                ? $fieldDeclaration
                : Field::ofType($fieldDeclaration);

            if (str_ends_with($name, '?')) {
                $fields[substr($name, 0, -1)] = $field->optional();
            } else {
                $fields[$name] = $field;
            }
        }

        return $fields;
    }

    /**
     * This is used internally to locate a field by its name.
     * @internal
     */
    public function getFieldByName(string $name): Field
    {
        return $this->fields()[$name];
    }

    /**
     * @return false|array<mixed>
     */
    private function passThroughConfig(): false|array
    {
        if (!$this->passThrough) {
            return false;
        }

        return [];
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
                    ...$field->getType()->toDefinition()->input(),
                    'description' => $field->getDescription(),
                    'deprecated' => $field->isDeprecated()
                ]), $this->fields),
                'additionalProperties' => $this->passThroughConfig(),
                'required' => $required,
            ],
            [
                'type' => 'object',
                'properties' => array_map(static fn(Field $field) => ([
                    ...$field->getType()->toDefinition()->output(),
                    'description' => $field->getDescription(),
                    'deprecated' => $field->isDeprecated()
                ]), $this->fields),
                'additionalProperties' => $this->passThroughConfig(),
                'required' => $required,
            ]
        );
    }

    public function parse(mixed $value, Context $context): mixed
    {
        $parsed = [];
        $isDirty = false;
        foreach ($this->fields() as $name => $field) {
            $context->enter($name);
            try {
                $fieldValue = $field->resolveValue($name, $value, $context->userProvidedContext);

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

        if (!$this->runValidators($parsed, $context)) {
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
