<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use Closure;
use TypescriptSchema\Definition\BaseType;
use TypescriptSchema\Definition\Shared\IsNullable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

/**
 * @template T
 */
abstract class PrimitiveType extends BaseType
{
    use IsNullable;

    protected bool $coerce = false;

    /**
     *
     *
     * @param mixed $value
     * @return mixed
     */
    abstract protected function parsePrimitiveType(mixed $value): mixed;

    abstract protected function coerceValue(mixed $value): mixed;

    /**
     * @var T|Closure(): T|null
     */
    protected mixed $defaultValue = null;

    public function coerce(): static
    {
        $instance = clone $this;
        $instance->coerce = true;
        return $instance;
    }

    /**
     * Set a default value. You can set a static closure if needed.
     * Make sure that the value is valid, as it will go through all validators and transformers afterwards.
     *
     * @param T|Closure(): T $value
     * @return $this
     */
    public function default(mixed $value): static
    {
        $instance = clone $this;
        $instance->defaultValue = $value === null
            ? null
            : $value;
        return $instance;
    }

    /**
     * @return mixed
     * @internal
     */
    public function getDefaultValue(): mixed
    {
        if (!isset($this->defaultValue)) {
            return null;
        }

        return $this->defaultValue instanceof Closure
            ? ($this->defaultValue)()
            : $this->defaultValue;
    }

    /**
     * @return bool
     * @internal
     */
    public function hasDefaultValue(): bool
    {
        return isset($this->defaultValue);
    }

    public static function make(): static
    {
        return new static();
    }

    final protected function validateAndParseType(mixed $value, Context $context): mixed
    {
        $value ??= $this->getDefaultValue();

        if (null === $value) {
            throw Issue::invalidType($this->toDefinition()->input, $value)->fatal();
        }

        /** @var T $value */
        return $this->parsePrimitiveType(
            $this->coerce ? $this->coerceValue($value) : $value,
        );
    }
}
