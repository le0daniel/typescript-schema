<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use Throwable;
use TypescriptSchema\Contracts\ComplexType;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\ClosureValidator;
use TypescriptSchema\Helpers\Context;

final class RefineType implements ComplexType
{
    use Refinable, Transformable;

    public function __construct(
        private readonly Type             $type,
        private readonly ClosureValidator $validator,
    )
    {
    }

    public function resolve(mixed $value, Context $context): mixed
    {
        $value = Executor::execute($this->type, $value, $context);
        if ($value === Value::INVALID) {
            return Value::INVALID;
        }

        try {
            if (!$this->validator->validate($value)) {
                $context->addIssue($this->validator->produceIssue($value));
                return Value::INVALID;
            }
        } catch (Throwable $exception) {
            $context->addIssue(Issue::captureThrowable($exception));
            return Value::INVALID;
        }

        return $value;
    }

    public function toDefinition(): SchemaDefinition
    {
        return $this->type->toDefinition();
    }
}