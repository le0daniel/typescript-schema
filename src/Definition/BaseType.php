<?php declare(strict_types=1);

namespace TypescriptSchema\Definition;

use Throwable;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Shared\InternalTransformers;
use TypescriptSchema\Definition\Shared\ParsesInput;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Definition\Shared\Validators;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

abstract class BaseType implements Type
{
    use InternalTransformers, Transformable, Validators, Refinable, ParsesInput;

    /**
     * Given a value and context, validate the value and return with the right type.
     * Coercion should happen here if needed. The returned value is used for further
     * validation. Any error thrown or Value::INVALID value returned stops any further process.
     *
     * **Important**: If `Value::INVALID` is returned, no Issue is captured. You are responsible
     * to add any issues to the context.
     *
     * Validators and transformers are run on the value returned from this method.
     *
     * @param mixed|Value $value
     * @param Context $context
     * @throws Throwable
     * @return mixed
     */
    abstract protected function validateAndParseType(mixed $value, Context $context): mixed;

    abstract protected function toDefinition(): Definition;

    final public function toOutputDefinition(): string
    {
        if ($this->overwrittenOutputType) {
            return $this->overwrittenOutputType;
        }

        return $this->toDefinition()->output;
    }

    final public function toInputDefinition(): string
    {
        return $this->toDefinition()->input;
    }

    /**
     * This is the function run when parsing.
     * @internal
     */
    final public function execute(mixed $value, Context $context): mixed
    {
        try {
            $value = $this->validateAndParseType($value, $context);
            if ($value === Value::INVALID) {
                return Value::INVALID;
            }
        } catch (Throwable $exception) {
            $context->addIssue(Issue::captureThrowable($exception));
            return Value::INVALID;
        }

        if (!$this->runValidators($value, $context)) {
            return Value::INVALID;
        }

        if (!$this->runRefiners($value, $context)) {
            return Value::INVALID;
        }

        try {
            return $this->runInternalTransformers($value);
        } catch (Throwable $exception) {
            $context->addIssue(Issue::captureThrowable($exception));
            return Value::INVALID;
        }
    }

}