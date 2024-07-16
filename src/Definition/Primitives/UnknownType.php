<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

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

    protected function toDefinition(): string
    {
        return 'unknown';
    }
}
