<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

use TypescriptSchema\Data\Schema\Definition;

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

    private function applyCoerceToInputDefinition(Definition $definition, ?array $acceptableInput = null): Definition
    {
        if (!$this->coerce || !$acceptableInput) {
            return $definition;
        }

        return new Definition(
            $acceptableInput,
            $definition->output(),
        );
    }

    protected function applyCoercionIfEnabled(mixed $value): mixed
    {
        return $this->coerce ? $this->coerceValue($value) : $value;
    }

    abstract protected function coerceValue(mixed $value): mixed;
}