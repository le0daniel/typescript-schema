<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use Closure;
use RuntimeException;
use TypescriptSchema\Contracts\ComplexType;
use TypescriptSchema\Contracts\OptionallyNamed;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Schema\Definition;
use TypescriptSchema\Definition\Shared\BaseType;
use TypescriptSchema\Definition\Shared\Nameable;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\Context;

final class UnionType implements Type, ComplexType, OptionallyNamed
{
    /** @use Nullable<UnionType> */
    use Nullable, Refinable, Transformable, BaseType, Nameable;

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

    public function getType(int|string $key): Type
    {
        return $this->types[$key];
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

    /**
     * @param mixed $value
     * @param Context $context
     * @return Type
     */
    private function resolveByClosure(mixed $value, Context $context): Type
    {
        $keyOrIndex = ($this->resolveType)($value, $context->userProvidedContext);
        if (is_int($keyOrIndex) && !array_is_list($this->types)) {
            $key = array_keys($this->types)[$keyOrIndex];
            return $this->types[$key];
        }

        return $this->types[$keyOrIndex];
    }

    private function parseInPartialMode(mixed $value, Context $context): mixed
    {
        $allIssues = [];
        /** @var null|Context $nullableValidationContext */
        $nullableValidationContext = null;

        foreach ($this->types as $type) {
            $validationContext = $context->cloneForProbing();
            $result = Executor::execute($type, $value, $validationContext);

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
        $context->addIssue(Issue::custom('Could not match union to any type'));
        return Value::INVALID;
    }

    public function toDefinition(): SchemaDefinition
    {
        return new Definition(
            [
                'oneOf' =>  array_map(fn(Type $type) => $type->toDefinition()->input(), $this->types)
            ],
            [
                'oneOf' =>  array_map(fn(Type $type) => $type->toDefinition()->output(), $this->types)
            ],
        );
    }

    public function parse(mixed $value, Context $context): mixed
    {
        if (isset($this->resolveType)) {
            return Executor::execute($this->resolveByClosure($value, $context), $value, $context);
        }

        if ($context->allowPartialFailures) {
            return $this->parseInPartialMode($value, $context);
        }

        $validationContext = $context->cloneForProbing();

        foreach ($this->types as $type) {
            $result = Executor::execute($type, $value, $validationContext);
            if ($result !== Value::INVALID) {
                return $result;
            }
        }

        // Add all issues that occurred during union resolving.
        $context->mergeProbingIssues($validationContext);
        $context->addIssue(Issue::custom("Value did not match any of the union types."));
        return Value::INVALID;
    }

    public function getTypes(): array
    {
        return $this->types;
    }
}
