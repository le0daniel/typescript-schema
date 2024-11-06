<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

interface Type
{
    public function toDefinition(): SchemaDefinition;
}
