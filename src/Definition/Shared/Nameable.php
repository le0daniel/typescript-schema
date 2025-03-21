<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

trait Nameable
{
    private string|null $name = null;

    public function name(string $name): static
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    public function getName(): string|null
    {
        return $this->name;

    }
}