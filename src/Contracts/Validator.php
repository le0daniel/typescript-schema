<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

use TypescriptSchema\Helpers\Context;

interface Validator
{

    /**
     * Given a value, validate it and add issues to the context.
     * You can add multiple issues if needed.
     *
     * Important, this should NOT throw.
     *
     * @param mixed $value
     * @param Context $context
     * @return bool
     */
    public function validate(mixed $value, Context $context): bool;
}