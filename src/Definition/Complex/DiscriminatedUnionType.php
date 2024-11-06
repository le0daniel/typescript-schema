<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use Generator;
use RuntimeException;
use Throwable;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Schema\Definition;
use TypescriptSchema\Definition\Primitives\LiteralType;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Definition\Wrappers\NullableWrapper;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\Context;

final class DiscriminatedUnionType implements Type
{
    /** @use Nullable<DiscriminatedUnionType> */
    use Nullable, Refinable, Transformable;

    /**
     * @param string $discriminatorFieldName
     * @param array<int|string, ObjectType> $types
     */
    public function __construct(
        private readonly string $discriminatorFieldName,
        private readonly array $types,
    )
    {
        if (count($this->types) < 2) {
            throw new RuntimeException("A discriminatory union type must have at least two types.");
        }
    }

    /**
     * @return Generator<Field>
     */
    public function discriminatorFields(): Generator
    {
        foreach ($this->types as $key => $type) {
            $field = $type->getFieldByName($this->discriminatorFieldName);
            if (!$field->getType() instanceof LiteralType) {
                throw new RuntimeException("Discriminatory union field type must be a literal type.");
            }
            yield $key => $field;
        }
    }

    public static function make(string $discriminatorFieldName, ObjectType ... $types): self
    {
        return new self($discriminatorFieldName, $types);
    }

    private function findMatchingType(mixed $value, Context $context): ?Type
    {
        foreach ($this->discriminatorFields() as $key => $field) {
            try {
                $fieldValue = $field->resolveValue($this->discriminatorFieldName, $value);
                $result = Executor::execute($field->getType(), $fieldValue, $context);
                if ($result === Value::INVALID) {
                    continue;
                }
            } catch (Throwable $exception) {
                $context->addIssue(Issue::captureThrowable($exception));
                continue;
            }

            return $this->types[$key];
        }

        return null;
    }

    public function toDefinition(): SchemaDefinition
    {
        return new Definition([
            'oneOf' => array_map(fn(Type $type) => $type->toDefinition()->input(), $this->types),
        ], [
            'oneOf' => array_map(fn(Type $type) => $type->toDefinition()->output(), $this->types),
        ]);
    }

    public function parse(mixed $value, Context $context): mixed
    {
        if ($value === null) {
            $context->addIssue(Issue::invalidType('array', $value));
            return Value::INVALID;
        }

        $matchedType = $this->findMatchingType($value, $probingContext = $context->cloneForProbing());

        if (!$matchedType) {
            $context->mergeProbingIssues($probingContext);
            $context->addIssue(Issue::custom("Value did not match the union types (field: {$this->discriminatorFieldName})."));
            return Value::INVALID;
        }

        return Executor::execute($matchedType, $value, $context);
    }
}
