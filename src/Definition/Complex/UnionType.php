<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use Closure;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\BaseType;
use TypescriptSchema\Definition\Shared\IsNullable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

final class UnionType extends BaseType
{
    use IsNullable;

    /**
     * @var Closure(mixed):(int|string)
     */
    private Closure $resolveType;

    /**
     * @param array<Type> $types
     */
    public function __construct(private readonly array $types)
    {
    }

    public static function make(Type...$types): self
    {
        return new self($types);
    }

    /**
     * Define a closure that resolves the correct type based on the
     * data passed in. If you use named parameters, you need to return
     * a string, otherwise the index of the type.
     *
     * Example:
     *
     *     $type = UnionType::make(StringType::make(), IntType::make());
     *     // This will always resolve the union to the type with index 1, so the IntType.
     *     $type->resolveTypeBy(fn($value) => 1);
     *
     *     // With named arguments, you can use the index or name directly.
     *     $type = UnionType::make(string: StringType::make(), int: IntType::make());
     *     $type->resolveTypeBy(fn($value) => 'string');
     *
     * @param Closure(mixed):(int|string) $resolveType
     * @return $this
     */
    public function resolveTypeBy(Closure $resolveType): self
    {
        $instance = clone $this;
        $instance->resolveType = $resolveType;
        return $instance;
    }

    protected function validateAndParseType(mixed $value, Context $context): mixed
    {
        if (isset($this->resolveType)) {
            return $this->resolveByClosure($value, $context);
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

    private function resolveByClosure(mixed $value, Context $context): mixed
    {
        $keyOrIndex = ($this->resolveType)($value);
        if (is_int($keyOrIndex) && !array_is_list($this->types)) {
            $key = array_keys($this->types)[$keyOrIndex];
            return $this->types[$key]->execute($value, $context);
        }

        $type = $this->types[($this->resolveType)($value)];
        return $type->execute($value, $context);
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

        $context->addIssues(...$allIssues);
        throw Issue::custom('Could not match union to any type');
    }

    public function toDefinition(): Definition
    {
        return Definition::join('|', ... $this->types);
    }
}
