<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Wrappers;

use Closure;
use Throwable;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Shared\IsNullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

final class TransformWrapper extends WrapsType
{
    use IsNullable, Refinable, Transformable;

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
        if ($resolvedValue === Value::INVALID) {
            return Value::INVALID;
        }

        try {
            $transformedValue = ($this->transformer)($resolvedValue);
        } catch (Throwable $throwable) {
            $context->addIssue(Issue::captureThrowable($throwable));
            return Value::INVALID;
        }

        if (!$this->runRefiners($transformedValue, $context)) {
            return Value::INVALID;
        }

        return $transformedValue;
    }

    protected function verifyType(Type $type): void
    {
    }

    public function toDefinition(): Definition
    {
        return $this->type->toDefinition()
            ->overwriteOutput($this->outputDefinition);
    }
}