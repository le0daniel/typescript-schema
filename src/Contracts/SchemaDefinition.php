<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

interface SchemaDefinition
{
    /**
     * @return array<mixed>
     */
    public function input(): array;

    /**
     * @return array<mixed>
     */
    public function output(): array;
}