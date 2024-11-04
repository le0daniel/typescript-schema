<?php declare(strict_types=1);

namespace TypescriptSchema\Data;

use TypescriptSchema\Contracts\SchemaDefinition;

final class WrappedDefinition implements SchemaDefinition
{

    public function __construct(
        private readonly SchemaDefinition $definition,
        private readonly \Closure         $toInput,
        private readonly \Closure         $toOutput,
    )
    {
    }

    public static function same(SchemaDefinition $definition, \Closure $closure)
    {
        return new self($definition, $closure, $closure);
    }

    public function toInputSchema(): array
    {
        return ($this->toInput)($this->definition->toInputSchema());
    }

    public function toOutputSchema(): array
    {
        return ($this->toOutput)($this->definition->toOutputSchema());
    }
}