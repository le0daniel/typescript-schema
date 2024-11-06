<?php declare(strict_types=1);

namespace TypescriptSchema\Data\Schema;

use TypescriptSchema\Contracts\SchemaDefinition;

final readonly class NullableDefinition implements SchemaDefinition
{

    public function __construct(private SchemaDefinition $definition)
    {
    }

    public function input(): array
    {
        return [
            'anyOf' => [
                $this->definition->input(),
                ['type' => 'null']
            ],
        ];
    }

    public function output(): array
    {
        return [
            'anyOf' => [
                $this->definition->output(),
                ['type' => 'null']
            ],
        ];
    }
}