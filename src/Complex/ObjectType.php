<?php declare(strict_types=1);

namespace TypescriptSchema\Complex;

use Closure;
use Throwable;
use TypescriptSchema\BaseType;
use TypescriptSchema\Context\Context;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\IsNullable;
use TypescriptSchema\Type;

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
                $fieldValue = $field->resolveToValue($name, $value);

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
            }
            finally {
                $context->leave();
            }
        }

        if ($this->passThrough && is_array($value)) {
            return array_merge($value, $parsed);
        }

        return $parsed;
    }

    /**
     * @internal
     */
    public function getFieldByName(string $name): Field
    {
        return $this->definition[$name];
    }

    public function toDefinition(): string
    {
        $definitions = [];
        foreach ($this->fields() as $name => $field) {
            $typeName = $field->isOptional() ? "{$name}?" : $name;
            $definitions[] = "{$typeName}: {$field->getType()->toDefinition()};";
        }

        if ($this->passThrough) {
            $definitions[] = '[key: string]: unknown;';
        }

        return '{' . implode(' ', $definitions) . '}';
    }
}
