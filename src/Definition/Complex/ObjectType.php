<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use Closure;
use Throwable;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\BaseType;
use TypescriptSchema\Definition\Shared\IsNullable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

final class ObjectType extends BaseType
{
    use IsNullable;

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

    public static function make(array|Closure $definition): self
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
     * @param mixed $value
     * @param Context $context
     * @return array|null
     * @internal
     */
    protected function validateAndParseType(mixed $value, Context $context): array|Value
    {
        if ($value === null) {
            $context->addIssue(Issue::invalidType('array', $value));
            return Value::INVALID;
        }

        $parsed = [];

        $isDirty = false;
        foreach ($this->fields() as $name => $field) {
            $context->enter($name);
            try {
                $fieldValue = $field->resolveValue($name, $value);

                if ($field->isOptional() && $fieldValue === Value::UNDEFINED) {
                    continue;
                }

                $parsedValue = $field->getType()->execute(Value::undefinedToNull($fieldValue), $context);
                if ($parsedValue === Value::INVALID) {
                    $isDirty = true;
                    continue;
                }

                $parsed[$name] = $parsedValue;
            } catch (Throwable $exception) {
                $context->addIssue(Issue::captureThrowable($exception));
                return Value::INVALID;
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

        $valuesToPassThrough = match (true) {
            $this->passThrough instanceof Closure => ($this->passThrough)($value),
            is_array($value) => $value,
            default => []
        };

        return [
            ... $valuesToPassThrough,
            ... $parsed,
        ];
    }

    /**
     * This is used internally to locate a field by its name.
     * @internal
     */
    public function getFieldByName(string $name): Field
    {
        return $this->fields()[$name];
    }

    public function toDefinition(): Definition
    {
        $definitions = [];

        foreach ($this->fields() as $name => $field) {
            $typeName = $field->isOptional() ? "{$name}?" : $name;
            $definitions[] = [
                $field->isIsOnlyOutput() ? '' : "{$field->getDocBlock()}{$typeName}: {$field->getType()->toDefinition()->input};",
                "{$field->getDocBlock()}{$typeName}: {$field->getType()->toDefinition()->output};",
            ];
        }

        if ($this->passThrough) {
            $definitions[] = [
                '[key: string]: unknown;',
                '[key: string]: unknown;',
            ];
        }

        return new Definition(
            '{' . implode(' ', array_column($definitions, 0)) . '}',
            '{' . implode(' ', array_column($definitions, 1)) . '}'
        );
    }
}
