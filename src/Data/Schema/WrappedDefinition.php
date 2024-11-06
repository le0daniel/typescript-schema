<?php declare(strict_types=1);

namespace TypescriptSchema\Data\Schema;

use Closure;
use TypescriptSchema\Contracts\SchemaDefinition;

final readonly class WrappedDefinition implements SchemaDefinition
{

    public function __construct(
        private SchemaDefinition $definition,
        private Closure          $toInput,
        private Closure          $toOutput,
    )
    {
    }

    public static function same(SchemaDefinition $definition, Closure $closure): WrappedDefinition
    {
        return new self($definition, $closure, $closure);
    }

    public function input(): array
    {
        return ($this->toInput)($this->definition->input());
    }

    public function output(): array
    {
        return ($this->toOutput)($this->definition->output());
    }
}