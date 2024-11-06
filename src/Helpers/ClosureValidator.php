<?php declare(strict_types=1);

namespace TypescriptSchema\Helpers;

use Closure;
use Throwable;
use TypescriptSchema\Contracts\Validator;
use TypescriptSchema\Exceptions\Issue;

final readonly class ClosureValidator implements Validator
{

    public function __construct(
        private Closure             $validationFunction,
        private null|string|Closure $message = null,
    )
    {
    }

    public function validate(mixed $value, Context $context): bool
    {
        try {
            $result = ($this->validationFunction)($value) === true;
            if (!$result) {
                $context->addIssue($this->produceIssue($value));
            }
            return $result;
        } catch (Throwable $issue) {
            $context->addIssue(Issue::captureThrowable($issue));
            return false;
        }
    }

    /**
     * @param mixed $invalidValue
     * @return Issue
     * @throws Throwable
     */
    private function produceIssue(mixed $invalidValue): Issue
    {
        if (!$this->message) {
            return Issue::generic();
        }

        if (is_string($this->message)) {
            return Issue::custom($this->message);
        }

        return ($this->message)($invalidValue);
    }
}