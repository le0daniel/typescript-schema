<?php declare(strict_types=1);

namespace TypescriptSchema;

use RuntimeException;
use TypescriptSchema\Contracts\ComplexType;
use TypescriptSchema\Contracts\LeafType;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\ExecutionMode;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\Context;

final class Schema
{
    public function __construct(private readonly Type $type)
    {
    }

    public static function make(Type $type): self
    {
        return new self($type);
    }

    public function serialize(mixed $data, array $options = [])
    {
        return Executor::execute($this->type, $data, new Context(mode: ExecutionMode::SERIALIZE));
    }

    public function parse(mixed $data, array $options = [])
    {
        return Executor::execute($this->type, $data, new Context(mode: ExecutionMode::PARSE));
    }

    public function toDefinition(): SchemaDefinition
    {
        return $this->type->toDefinition();
    }
}