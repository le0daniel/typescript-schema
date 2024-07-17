<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use TypescriptSchema\Data\Definition;
use TypescriptSchema\Definition\BaseType;
use TypescriptSchema\Helpers\Context;

final class UnknownType extends BaseType
{
    public static function make(): self
    {
        return new self();
    }

    protected function validateAndParseType(mixed $value, Context $context): mixed
    {
        return $value;
    }

    protected function toDefinition(): Definition
    {
        return Definition::same('unknown');
    }
}
