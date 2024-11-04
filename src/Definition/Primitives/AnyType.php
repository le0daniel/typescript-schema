<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use TypescriptSchema\Contracts\LeafType;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Helpers\Context;

final class AnyType implements LeafType
{
    public static function make(): self
    {
        return new self();
    }

    public function toDefinition(): SchemaDefinition
    {
        return Definition::same([]);
    }

    public function parseAndValidate(mixed $value, Context $context): mixed
    {
        return $value;
    }

    public function validateAndSerialize(mixed $value, Context $context): mixed
    {
        return $value;
    }
}
