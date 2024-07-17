<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Wrappers;

use RuntimeException;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Primitives\PrimitiveType;
use TypescriptSchema\Helpers\Context;

/**
 * @template T of Type
 * @mixin Type
 */
final class NullableWrapper extends WrapsType
{

    /**
     * @template M of Type
     * @param Type $type
     * @return Type
     */
    public static function make(Type $type): NullableWrapper
    {
        return new self($type);
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
     * @param array $arguments
     * @return $this
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

    public function execute(mixed $value, Context $context): mixed {
        // If a value is set and partial errors are enabled, an error boundary is created
        if ($value !== null) {
            $result = $this->type->execute($value, $context);
            return $context->allowPartialFailures && $result === Value::INVALID
                ? null
                : $result;
        }

        // If the type has a default value defined, we execute the type.
        // Otherwise, we skip it and just return null.

        /** @var Type $realType */
        $realType = $this->type instanceof WrapsType ? $this->type->mostInnerType() : $this->type;
        if ($realType instanceof PrimitiveType && $realType->hasDefaultValue()) {
            return $this->type->execute(null, $context);
        }

        return null;
    }

    public function toInputDefinition(): string
    {
        return $this->type->toInputDefinition() . '|null';
    }

    public function toOutputDefinition(): string
    {
        return $this->type->toOutputDefinition() . '|null';
    }
}
