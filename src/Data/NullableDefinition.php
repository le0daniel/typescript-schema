<?php declare(strict_types=1);

namespace TypescriptSchema\Data;

use TypescriptSchema\Contracts\SchemaDefinition;

final readonly class NullableDefinition implements SchemaDefinition
{

    public function __construct(private SchemaDefinition $definition)
    {
    }

    public function toInputSchema(): array
    {
        return [
            'anyOf' => [
                $this->definition->toInputSchema(),
                ['type' => 'null']
            ],
        ];
    }

    public function toOutputSchema(): array
    {
        return [
            'anyOf' => [
                $this->definition->toOutputSchema(),
                ['type' => 'null']
            ],
        ];
    }
}