<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

use TypescriptSchema\Exceptions\Issue;

interface Validator
{

    /**
     * Given a value, return true or false showing if the value is correct or not.
     *
     * @param mixed $value
     * @throws Issue
     * @return bool
     */
    public function validate(mixed $value): bool;

    /**
     * In case the validation failed, produce an issue that is registered correctly.
     *
     * If no further validation should happen, use `$issue->fatal()`
     *
     * @param mixed $invalidValue
     * @return Issue
     */
    public function produceIssue(mixed $invalidValue): Issue;

}