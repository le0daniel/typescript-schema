<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use Closure;
use Throwable;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Definition\BaseType;
use TypescriptSchema\Definition\Shared\IsNullable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

final class ObjectType extends BaseType
{
    use IsNullable;

    private bool $passThrough = false;

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
     * @api
     * @return $this
     */
    public function passThrough(): ObjectType
    {
        $instance = clone $this;
        $instance->passThrough = true;
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
        $parsed = [];

        foreach ($this->fields() as $name => $field) {
            $context->enter($name);
            try {
                $fieldValue = $field->resolveValue($name, $value);

                if ($field->isOptional() && $fieldValue === Value::UNDEFINED) {
                    continue;
                }

                $parsedValue = $field->getType()->execute(Value::undefinedToNull($fieldValue), $context);
                if ($parsedValue === Value::INVALID) {
                    return Value::INVALID;
                }

                $parsed[$name] = $parsedValue;
            } catch (Throwable $exception) {
                $context->addIssue(Issue::captureThrowable($exception));
                return Value::INVALID;
            } finally {
                $context->leave();
            }
        }

        if ($this->passThrough && is_array($value)) {
            return [
                ... $value,
                ... $parsed,
            ];
        }

        return $parsed;
    }

    /**
     * This is used internally to locate a field by its name.
     * @internal
     */
    public function getFieldByName(string $name): Field
    {
        return $this->definition[$name];
    }

    protected function toDefinition(): Definition
    {
        $definitions = [];

        foreach ($this->fields() as $name => $field) {
            $typeName = $field->isOptional() ? "{$name}?" : $name;
            $definitions[] = [
                "{$typeName}: {$field->getType()->toInputDefinition()};",
                "{$typeName}: {$field->getType()->toOutputDefinition()};",
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
