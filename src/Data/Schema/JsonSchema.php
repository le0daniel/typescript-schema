<?php declare(strict_types=1);

namespace TypescriptSchema\Data\Schema;

final readonly class JsonSchema
{
    public function __construct(
        public array  $definition,
        public ?array $metadata = null,
        public ?array $tags = null,
    )
    {
    }

    public function change(array $definition): JsonSchema
    {
        return new JsonSchema($definition, $this->metadata, $this->tags);
    }

    public function toArray(): array
    {
        $definition = $this->definition;
        if (!empty($this->metadata)) {
            $definition['__metadata'] = $this->metadata;
        }
        if (!empty($this->tags)) {
            $definition['__tags'] = $this->tags;
        }
        return $definition;
    }
}