<?php declare(strict_types=1);

namespace TypescriptSchema\Primitives;

use TypescriptSchema\BaseType;
use TypescriptSchema\Context\Context;

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
