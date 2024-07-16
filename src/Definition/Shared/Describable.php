<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

trait Describable
{
    private readonly string|null $description;
    public function getDescription(): string|null {
        return $this->description;
    }

    public function describe(string $description): static
    {
        $instance = clone $this;
        $instance->description = $description;
        return $instance;
    }
}