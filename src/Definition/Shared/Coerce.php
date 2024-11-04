<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

trait Coerce
{

    private bool $coerce = false;

    /**
     * Coerce a value. It ONLY applies on parsing, not on serialization.
     *
     * @param bool $coerce
     * @return $this
     */
    public function coerce(bool $coerce = true): static
    {
        $clone = clone $this;
        $clone->coerce = $coerce;
        return $clone;
    }

    protected function applyCoercionIfEnabled(mixed $value): mixed
    {
        return $this->coerce ? $this->coerceValue($value) : $value;
    }

    abstract protected function coerceValue(mixed $value): mixed;
}