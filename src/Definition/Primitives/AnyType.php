<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use TypescriptSchema\Definition\BaseType;
use TypescriptSchema\Helpers\Context;

final class AnyType extends BaseType
{
    public static function make(): self
    {
        return new self();
    }

    protected function toDefinition(): string
    {
        return 'any';
    }

    protected function validateAndParseType(mixed $value, Context $context): mixed
    {
        return $value;
    }
}
