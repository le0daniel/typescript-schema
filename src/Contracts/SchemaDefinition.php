<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

interface SchemaDefinition
{
    public function input(): array;

    public function output(): array;
}