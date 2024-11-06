<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

use Closure;
use TypescriptSchema\Contracts\Type;

/**
 * @template T of Type
 */
trait HasDefaultValue
{

    private mixed $defaultValue = null;

    public function defaultValue(mixed $defaultValue): static
    {
        $clone = clone $this;
        $clone->defaultValue = $defaultValue;
        return $clone;
    }

    protected function applyDefaultValue(mixed $value): mixed
    {
        if (null !== $value) {
            return $value;
        }

        return $this->defaultValue instanceof Closure
            ? ($this->defaultValue)($value)
            : $this->defaultValue;
    }
}