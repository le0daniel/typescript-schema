<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use Closure;
use Throwable;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Schema\Definition;
use TypescriptSchema\Definition\Shared\BaseType;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\Context;

final class TransformType implements Type
{
    use Refinable, Transformable, BaseType;

    /**
     * @param Type $type
     * @param Closure $transformation
     * @param array<mixed>|Closure(array<mixed>): array<mixed>|null $outputSchema
     */
    public function __construct(
        private readonly Type $type,
        private readonly Closure $transformation,
        private readonly null|array|Closure $outputSchema = null,
    )
    {
    }

    public function parse(mixed $value, Context $context): mixed
    {
        $value = Executor::execute($this->type, $value, $context);

        if ($value === Value::INVALID) {
            return Value::INVALID;
        }

        try {
            return ($this->transformation)($value);
        } catch (Throwable $exception) {
            $context->addIssue(Issue::captureThrowable($exception));
            return Value::INVALID;
        }
    }

    public function toDefinition(): SchemaDefinition
    {
        return new Definition(
            $this->type->toDefinition()->input(),
            match (true) {
                is_null($this->outputSchema) => [],
                is_array($this->outputSchema) => $this->outputSchema,
                $this->outputSchema instanceof Closure => ($this->outputSchema)($this->type->toDefinition()->output())
            }
        );
    }
}