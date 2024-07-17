<?php declare(strict_types=1);

namespace TypescriptSchema\Data;

use Closure;
use TypescriptSchema\Contracts\Type;

final readonly class Definition implements \Stringable
{

    public function __construct(
        public readonly string $input,
        public readonly string $output,
    )
    {
    }

    public static function same(string $definition): self
    {
        return new self($definition, $definition);
    }

    public function __toString(): string
    {
        return $this->output;
    }

    public static function join(string $joiner, Type ... $definitions): self
    {
        return new self(
            implode($joiner, array_map(fn(Type $definition) => $definition->toDefinition()->input, $definitions)),
            implode($joiner, array_map(fn(Type $definition) => $definition->toDefinition()->output, $definitions)),
        );
    }

    public function wrap(Closure $wrapper): self
    {
        return new self(
            $wrapper($this->input),
            $wrapper($this->output),
        );
    }

    public function overwriteOutput(string|Closure|null $output): self
    {
        return new self(
            $this->input,
            match (true) {
                is_string($output) => $output,
                $output instanceof Closure => $output($this),
                default => 'unknown',
            }
        );
    }
}