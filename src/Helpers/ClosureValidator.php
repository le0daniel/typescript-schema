<?php declare(strict_types=1);

namespace TypescriptSchema\Helpers;

use Closure;
use TypescriptSchema\Contracts\Validator;
use TypescriptSchema\Exceptions\Issue;

final class ClosureValidator implements Validator
{

    public function __construct(
        private readonly Closure $validationFunction,
        private readonly null|string|Closure $message = null,
    )
    {
    }

    public function validate(mixed $value): bool
    {
        return ($this->validationFunction)($value) === true;
    }

    public function produceIssue(mixed $invalidValue): Issue
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