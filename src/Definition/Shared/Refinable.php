<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

use Closure;
use Throwable;
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
     * @return static
     */
    public function refine(Closure $refine, string|Closure|null $message = null): static
    {
        $instance = clone $this;
        $instance->refiners[] = new ClosureValidator($refine, $message);
        return $instance;
    }

    private function runRefiners(mixed $value, Context $context): bool
    {
        $isDirty = false;
        foreach ($this->refiners as $validator) {
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

}