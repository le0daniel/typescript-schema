<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Shared\BaseType;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\ClosureValidator;
use TypescriptSchema\Helpers\Context;

final class RefineType implements Type
{
    use Refinable, Transformable, BaseType;

    public function __construct(
        private readonly Type             $type,
        private readonly ClosureValidator $validator,
    )
    {
    }

    public function parse(mixed $value, Context $context): mixed
    {
        $value = Executor::execute($this->type, $value, $context);
        if (!$context->shouldRunValidators()) {
            return $value;
        }

        if ($value === Value::INVALID) {
            return Value::INVALID;
        }

        if (!$this->validator->validate($value, $context)) {
            return Value::INVALID;
        }

        return $value;
    }

    public function toDefinition(): SchemaDefinition
    {
        return $this->type->toDefinition();
    }
}