<?php declare(strict_types=1);

namespace TypescriptSchema\Complex;

use Throwable;
use TypescriptSchema\BaseType;
use TypescriptSchema\Context\Context;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\IsNullable;
use TypescriptSchema\Type;

final class DiscriminatedUnionType extends BaseType
{
    use IsNullable;

    /**
     * @param string $discriminatorFieldName
     * @param array<ObjectType> $types
     */
    protected function __construct(
        private readonly string $discriminatorFieldName,
        private readonly array $types,
    )
    {
    }

    public static function make(string $discriminatorFieldName, ObjectType ... $types): self
    {
        return new self($discriminatorFieldName, $types);
    }

    protected function validateAndParseType(mixed $value, Context $context): mixed
    {
        $probingContext = $context->cloneForProbing();

        foreach ($this->types as $objectType) {
            try {
                $field = $objectType->getFieldByName($this->discriminatorFieldName);

                // Field passes successfully
                $result = $field->getType()->execute(
                    $field->resolveToValue($this->discriminatorFieldName, $value),
                    $probingContext
                );
            } catch (Throwable $throwable) {
                $probingContext->addIssue(Issue::captureThrowable($throwable));
                continue;
            }

            if ($result !== Value::INVALID) {
                return $objectType->execute($value, $context);
            }
        }

        $context->mergeProbingIssues($context);
        throw Issue::custom("Value did not match the union types (field: {$this->discriminatorFieldName}).");
    }

    public function toDefinition(): string
    {
        return implode('|', array_map(fn(Type $type) => $type->toDefinition(), $this->types));
    }
}
