<?php declare(strict_types=1);

namespace TypescriptSchema\Complex;

use TypescriptSchema\Context\Context;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Helpers\WrapsType;
use TypescriptSchema\Primitives\PrimitiveType;
use TypescriptSchema\Type;
use RuntimeException;

/**
 * @template T of Type
 * @mixin T
 */
final class NullableType extends WrapsType
{

    /**
     * @template M of Type
     * @param M $type
     * @return M
     */
    public static function make(Type $type): NullableType
    {
        return new self($type);
    }

    protected function verifyType(Type $type): void
    {
        if ($type instanceof $this) {
            throw new RuntimeException("Can not wrap a nullable type with nullable.");
        }
    }

    /**
     * Proxies all call forward to the type.
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
            return $context->partialResult && $result === Value::INVALID
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

    /**
     * Adds Null to the typescript definition.
     *
     * @return string
     */
    public function toDefinition(): string {
        return $this->type->toDefinition() . '|null';
    }
}
