<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Wrappers;

use Closure;
use Throwable;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Definition\Shared\InternalTransformers;
use TypescriptSchema\Definition\Shared\IsNullable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

final class TransformWrapper extends WrapsType
{
    use IsNullable, InternalTransformers;

    protected function __construct(
        Type                        $type,
        Closure                     $transformer,
        private string|Closure|null $outputDefinition = null,
    )
    {
        $this->internalTransformers = [$transformer];
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
        if ($value === Value::INVALID) {
            return Value::INVALID;
        }

        try {
            return $this->runInternalTransformers($resolvedValue);
        } catch (Throwable $throwable) {
            $context->addIssue(Issue::captureThrowable($throwable));
            return Value::INVALID;
        }
    }

    public function refine(Closure $closure, string|null|Closure $message = null): RefineWrapper
    {
        return RefineWrapper::make($this, $closure, $message);
    }

    public function transform(Closure $transformer, string|Closure|null $outputDefinition): TransformWrapper
    {
        $instance = $this->addInternalTransformer($transformer);

        if ($outputDefinition) {
            $instance->outputDefinition = $outputDefinition;
        }

        return $instance;
    }

    protected function verifyType(Type $type): void
    {
    }

    public function toInputDefinition(): string
    {
        return $this->type->toInputDefinition();
    }

    public function toOutputDefinition(): string
    {
        return match (true) {
            is_string($this->outputDefinition) => $this->outputDefinition,
            $this->outputDefinition instanceof Closure => ($this->outputDefinition)($this->type->toOutputDefinition()),
            // As the type is no longer known, it is returned as any.
            default => 'unknown',
        };
    }
}