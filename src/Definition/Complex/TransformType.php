<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use TypescriptSchema\Contracts\ComplexType;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\Context;

final class TransformType implements ComplexType
{
    use Refinable, Transformable;

    public function __construct(
        private readonly Type $type,
        private readonly \Closure $transformation,
        private readonly ?array $outputSchema,
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
            return ($this->transformation)($value);
        } catch (\Throwable $exception) {
            $context->addIssue(Issue::captureThrowable($exception));
            return Value::INVALID;
        }
    }

    public function toDefinition(): SchemaDefinition
    {
        return new Definition(
            $this->type->toDefinition()->toInputSchema(),
            // By default, transformations result in a type ANY.
            $this->outputSchema ?? [],
        );
    }
}