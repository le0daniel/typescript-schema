<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\BaseType;
use TypescriptSchema\Definition\Shared\IsNullable;
use TypescriptSchema\Definition\Wrappers\WrapsType;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

final class UnionType extends BaseType
{
    use IsNullable;

    /**
     * @param array<Type> $types
     */
    public function __construct(private readonly array $types)
    {}

    public static function make(Type... $types): self
    {
        return new self($types);
    }

    protected function validateAndParseType(mixed $value, Context $context): mixed
    {
        if ($value === null) {
            $context->addIssue(Issue::invalidType('array', $value));
            return Value::INVALID;
        }

        // Need to handle the partial mode differently, as null barriers will be accepted.
        if ($context->allowPartialFailures) {
            return $this->parseInPartialMode($value, $context);
        }

        $validationContext = $context->cloneForProbing();

        foreach ($this->types as $type) {
            $result = $type->execute($value, $validationContext);
            if ($result !== Value::INVALID) {
                return $result;
            }
        }

        // Add all issues that occurred during union resolving.
        $context->mergeProbingIssues($validationContext);

        throw Issue::custom("Value did not match any of the union types.");
    }

    private function parseInPartialMode(mixed $value, Context $context): mixed
    {
        $allIssues = [];
        /** @var null|Context $nullableValidationContext */
        $nullableValidationContext = null;

        foreach ($this->types as $type) {
            $validationContext = $context->cloneForProbing();
            $result = $type->execute($value, $validationContext);

            if ($result === Value::INVALID) {
                array_push($allIssues, ...$validationContext->getIssues());
                continue;
            }

            // Full match, or partial match below. This is considered a success.
            if (!$validationContext->hasIssues() || $result !== null) {
                $context->mergeProbingIssues($validationContext);
                return $result;
            }

            // The value is null, meaning we encountered issues, but there was a nullable barrier.
            if (!isset($nullableValidationContext)) {
                $nullableValidationContext = $validationContext;
            }
        }

        if ($nullableValidationContext) {
            $context->mergeProbingIssues($nullableValidationContext);
            return null;
        }

        foreach ($allIssues as $issue) {
            $context->addIssue($issue);
        }
        throw Issue::custom('Could not match union to any type');
    }

    public function toDefinition(): Definition
    {
        return Definition::join('|', ... $this->types);
    }
}
