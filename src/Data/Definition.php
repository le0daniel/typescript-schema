<?php declare(strict_types=1);

namespace TypescriptSchema\Data;

use Closure;

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
}