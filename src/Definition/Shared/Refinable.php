<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

use Closure;
use Throwable;
use TypescriptSchema\Definition\Complex\RefineType;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\ClosureValidator;
use TypescriptSchema\Helpers\Context;

trait Refinable
{
    /**
     * @var array<ClosureValidator>
     */
    private array $refiners = [];

    /**
     * Add a refining function that validates the input.
     *
     * @param Closure(mixed):bool $refine
     * @param string|Closure(mixed): Issue|null $message
     * @return RefineType
     */
    public function refine(Closure $refine, string|Closure|null $message = null): RefineType
    {
        return new RefineType($this, new ClosureValidator($refine, $message));
    }
}