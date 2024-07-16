<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

use Closure;
use Throwable;
use TypescriptSchema\Definition\Wrappers\RefineWrapper;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\ClosureValidator;
use TypescriptSchema\Helpers\Context;

trait Refinable
{
    /**
     * Add a refining function that validates the input.
     *
     * @param Closure(mixed):bool $refine
     * @param string|Closure(mixed): Issue|null $message
     * @return RefineWrapper
     */
    public function refine(Closure $refine, string|Closure|null $message = null): RefineWrapper
    {
        return RefineWrapper::make($this, $refine, $message);
    }

}