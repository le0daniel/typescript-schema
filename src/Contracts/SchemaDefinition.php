<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

interface SchemaDefinition
{
    public function toInputSchema(): array;

    public function toOutputSchema(): array;
}