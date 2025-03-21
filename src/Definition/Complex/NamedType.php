<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use TypescriptSchema\Contracts\ComplexType;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Helpers\Context;

final class NamedType implements Type, ComplexType, \TypescriptSchema\Contracts\NamedType
{
    public function __construct(
        public readonly string $name,
        public readonly Type $type
    )
    {
    }

    public function getTypes(): array
    {
        return [$this->type];
    }

    public function toDefinition(): SchemaDefinition
    {
        return $this->type->toDefinition();
    }

    public function parse(mixed $value, Context $context): mixed
    {
        return $this->type->parse($value, $context);
    }

    public function getName(): string
    {
        return $this->name;
    }
}