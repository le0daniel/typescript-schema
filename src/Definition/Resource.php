<?php declare(strict_types=1);

namespace TypescriptSchema\Definition;

use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Definition\Shared\BaseType;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Helpers\Context;

abstract class Resource implements Type
{
    use Nullable, BaseType;

    private Type $type;

    final public function __construct()
    {
        $this->type = static::type();
    }

    public abstract static function type(): Type;

    final public function toDefinition(): SchemaDefinition {
        return $this->type->toDefinition();
    }

    final public function parse(mixed $value, Context $context): mixed
    {
        return $this->type->parse($value, $context);
    }
}