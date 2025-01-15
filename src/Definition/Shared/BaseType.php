<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

use TypescriptSchema\Definition\Schema;

trait BaseType
{
    private ?array $metadata = null;
    private array $tags = [];

    public function toSchema(): Schema
    {
        return new Schema($this);
    }

    final public function addMetadata(string $key, mixed $value): static
    {
        $clone = clone $this;
        $clone->metadata ??= [];
        $clone->metadata[$key] = $value;
        return $clone;
    }

    final public function addTag(string ...$tags): static
    {
        $clone = clone $this;
        $clone->tags = array_values(array_unique([...$tags, ...$this->tags]));
        return $clone;
    }
}