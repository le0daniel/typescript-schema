<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use RuntimeException;
use Throwable;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\BaseType;
use TypescriptSchema\Definition\Primitives\LiteralType;
use TypescriptSchema\Definition\Shared\IsNullable;
use TypescriptSchema\Definition\Wrappers\NullableWrapper;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

final class DiscriminatedUnionType extends BaseType
{
    use IsNullable;

    /**
     * @param string $discriminatorFieldName
     * @param array<ObjectType|NullableWrapper<ObjectType>> $types
     */
    protected function __construct(
        private readonly string $discriminatorFieldName,
        private readonly array $types,
    )
    {
        if (count($this->types) < 2) {
            throw new RuntimeException("A discriminatory union type must have at least two types.");
        }
    }

    /**
     * @return \Generator<Field>
     */
    public function discriminatorFields(): \Generator
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
                $result = $field->getType()->execute($fieldValue, $context);
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

    protected function validateAndParseType(mixed $value, Context $context): mixed
    {
        $matchedType = $this->findMatchingType($value, $probingContext = $context->cloneForProbing());

        if (!$matchedType) {
            $context->mergeProbingIssues($probingContext);
            throw Issue::custom("Value did not match the union types (field: {$this->discriminatorFieldName}).");
        }

        return $matchedType->execute($value, $context);
    }

    public function toDefinition(): Definition
    {
        return Definition::join('|', ...$this->types);
    }
}
