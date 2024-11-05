<?php declare(strict_types=1);

namespace TypescriptSchema\Execution;

use RuntimeException;
use TypescriptSchema\Contracts\ComplexType;
use TypescriptSchema\Contracts\LeafType;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\ExecutionMode;
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
        if ($type instanceof LeafType) {
            return match ($context->mode) {
                ExecutionMode::PARSE => $type->parseAndValidate($data, $context),
                ExecutionMode::SERIALIZE => $type->validateAndSerialize($data, $context),
            };
        }

        if ($type instanceof ComplexType) {
            return $type->resolve($data, $context);
        }

        throw new RuntimeException("Failed to resolve.");
    }

}