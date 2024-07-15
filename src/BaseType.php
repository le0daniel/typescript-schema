<?php declare(strict_types=1);

namespace TypescriptSchema;

use Throwable;
use TypescriptSchema\Context\Context;
use TypescriptSchema\Data\TypescriptDefinition;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\ParsesInput;
use TypescriptSchema\Helpers\Refinable;
use TypescriptSchema\Helpers\InternalTransformers;
use TypescriptSchema\Helpers\Transformable;
use TypescriptSchema\Helpers\Validators;

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

    abstract protected function toDefinition(): string|TypescriptDefinition;

    final public function toOutputDefinition(): string
    {
        if ($this->overwrittenOutputType) {
            return $this->overwrittenOutputType;
        }

        $baseDefinition = $this->toDefinition();
        return $baseDefinition instanceof TypescriptDefinition
            ? $baseDefinition->output :
            $baseDefinition;
    }

    final public function toInputDefinition(): string
    {
        $baseDefinition = $this->toDefinition();
        return $baseDefinition instanceof TypescriptDefinition
            ? $baseDefinition->input :
            $baseDefinition;
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

        // This could be a wrapping type instead. Allowing to chain transform and refine
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