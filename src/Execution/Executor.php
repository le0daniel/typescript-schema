<?php declare(strict_types=1);

namespace TypescriptSchema\Execution;


use TypescriptSchema\Contracts\SerializesOutputValue;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\ExecutionMode;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Helpers\Context;

/**
 * @internal
 */
final class Executor
{

    /**
     * @param Type $type
     * @param mixed $data
     * @param Context $context
     * @return mixed
     * @internal
     */
    public static function execute(Type $type, mixed $data, Context $context): mixed
    {
        $value = $type->resolve($data, $context);
        if ($context->mode !== ExecutionMode::SERIALIZE || $value === Value::INVALID) {
            return $value;
        }

        return $type instanceof SerializesOutputValue
            ? $type->serializeValue($value, $context)
            : $value;
    }

}