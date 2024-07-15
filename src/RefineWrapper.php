<?php declare(strict_types=1);

namespace TypescriptSchema;

use Closure;
use Throwable;
use TypescriptSchema\Context\Context;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\ClosureValidator;
use TypescriptSchema\Helpers\IsNullable;
use TypescriptSchema\Helpers\Transformable;
use TypescriptSchema\Helpers\WrapsType;

final class RefineWrapper extends WrapsType
{
    use Transformable, IsNullable;

    private ClosureValidator $validator;

    protected function __construct(
        Type                                 $type,
        Closure             $refiner,
        string|Closure|null $message
    )
    {
        parent::__construct($type);
        $this->validator = new ClosureValidator($refiner, $message);
    }

    public static function make(Type $type, Closure $refiner, string|Closure|null $message = null): self
    {
        return new self($type, $refiner, $message);
    }

    public function execute(mixed $value, Context $context): mixed
    {
        $resolvedValue = $this->type->execute($value, $context);

        if ($resolvedValue === Value::INVALID) {
            return Value::INVALID;
        }

        try {
            if ($this->validator->validate($value)) {
                return $resolvedValue;
            }

            $context->addIssue($this->validator->produceIssue($value));
        } catch (Throwable $throwable) {
            $context->addIssue(Issue::captureThrowable($throwable));
        }

        return Value::INVALID;
    }

    public function toInputDefinition(): string
    {
        return $this->type->toInputDefinition();
    }

    public function toOutputDefinition(): string
    {
        return $this->type->toOutputDefinition();
    }

    protected function verifyType(Type $type): void
    {
        return;
    }
}