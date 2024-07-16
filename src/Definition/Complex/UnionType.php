<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Definition\BaseType;
use TypescriptSchema\Definition\Shared\IsNullable;
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

    protected function toDefinition(): string
    {
        return implode('|', array_map(fn(Type $type) => $type->toDefinition(), $this->types));
    }
}
