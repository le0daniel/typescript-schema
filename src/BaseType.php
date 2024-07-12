<?php declare(strict_types=1);

namespace TypescriptSchema;

use Closure;
use Throwable;
use TypescriptSchema\Context\Context;
use TypescriptSchema\Contracts\Validator;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\ParsesInput;
use TypescriptSchema\Helpers\Transformers;
use TypescriptSchema\Helpers\Validators;

abstract class BaseType implements Type
{
    use Transformers, Validators, ParsesInput;

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

    private function runValidationOn(array $validators, mixed $value, Context $context): bool
    {
        $isDirty = false;
        /** @var Validator|Closure $validator */
        foreach ($validators as $validator) {
            try {
                if ($validator->validate($value)) {
                    continue;
                }

                $issue = $validator->produceIssue($value);
            } catch (Throwable $exception) {
                $issue = Issue::captureThrowable($exception);
            }

            $isDirty = true;
            $context->addIssue($issue);
            if ($issue->isFatal()) {
                return false;
            }
        }

        return !$isDirty;
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

        if (
            !$this->runValidationOn($this->validators, $value, $context)
            || !$this->runValidationOn($this->refiners, $value, $context)
        ) {
            return Value::INVALID;
        }

        try {
            return $this->runTransformers($value);
        } catch (Throwable $exception) {
            $context->addIssue(Issue::captureThrowable($exception));
            return Value::INVALID;
        }
    }

}