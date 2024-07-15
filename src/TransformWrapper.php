<?php declare(strict_types=1);

namespace TypescriptSchema;

use Closure;
use TypescriptSchema\Context\Context;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Helpers\IsNullable;
use TypescriptSchema\Helpers\Refinable;
use TypescriptSchema\Helpers\Transformable;
use TypescriptSchema\Helpers\WrapsType;

final class TransformWrapper extends WrapsType
{
    use IsNullable, Transformable, Refinable;

    protected function __construct(
        Type                                 $type,
        private readonly Closure             $transformer,
        private readonly string|Closure|null $outputDefinition = null,
    )
    {
        parent::__construct($type);
    }

    public static function make(
        Type                $type,
        Closure             $transformer,
        string|Closure|null $outputDefinition = null,
    ): TransformWrapper
    {
        return new self($type, $transformer, $outputDefinition);
    }

    public function execute(mixed $value, Context $context): mixed
    {
        $resolvedValue = $this->type->execute($value, $context);
        return $resolvedValue === Value::INVALID
            ? Value::INVALID
            : ($this->transformer)($resolvedValue);
    }

    protected function verifyType(Type $type): void
    {}

    public function toInputDefinition(): string
    {
        return $this->type->toInputDefinition();
    }

    public function toOutputDefinition(): string
    {
        return match (true) {
            is_string($this->outputDefinition) => $this->outputDefinition,
            $this->outputDefinition instanceof Closure => ($this->outputDefinition)($this->type->toOutputDefinition()),
            default => $this->type->toOutputDefinition(),
        };
    }
}