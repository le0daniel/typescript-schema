<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Wrappers;

use RuntimeException;
use Throwable;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Schema\NullableDefinition;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\Context;

/**
 * @template-covariant T of Type
 * @mixin T
 */
final class NullableWrapper extends WrapsType
{
    /**
     * @template M of Type
     * @param M $type
     * @return NullableWrapper<M>
     */
    public static function make(Type $type): NullableWrapper
    {
        /** @var NullableWrapper<M> $type */
        $type = new self($type);
        return $type;
    }

    protected function verifyType(Type $type): void
    {
        if ($this->containsWrapped(NullableWrapper::class)) {
            throw new RuntimeException("Can not wrap a nullable type with nullable.");
        }
    }

    /**
     * Proxies all call forward to the type. This also ensures that the nullable wrapper stays on
     * top of all other wrappers.
     *
     * @param string $name
     * @param array<mixed> $arguments
     * @return NullableWrapper<T>|mixed
     */
    final public function __call(string $name, array $arguments)
    {
        // Ensures reusability by cloning deep when a type is returned from
        // a called method.
        $returnValue = $this->type->{$name}(...$arguments);
        if ($returnValue instanceof Type) {
            $this->verifyType($returnValue);
            $instance = clone $this;
            $instance->type = $returnValue;
            return $instance;
        }

        return $returnValue;
    }

    public function toDefinition(): SchemaDefinition
    {
        return new NullableDefinition($this->type->toDefinition());
    }

    public function parse(mixed $value, Context $context): mixed
    {
        if ($value === null) {
            return null;
        }

        try {
            $result = Executor::execute($this->type, $value, $context);
            if ($context->allowPartialFailures && $result === Value::INVALID) {
                return null;
            }
            return $result;
        } catch (Throwable $exception) {
            $context->addIssue(Issue::captureThrowable($exception));
            return $context->allowPartialFailures ? null : Value::INVALID;
        }
    }
}
