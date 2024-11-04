<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Helpers\Context;

interface Type
{
    public function toDefinition(): SchemaDefinition;
}
