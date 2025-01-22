<?php declare(strict_types=1);

namespace TypescriptSchema\Helpers;

use TypescriptSchema\Data\Enum\ExecutionMode;
use TypescriptSchema\Exceptions\Issue;

final class Context
{
    /** @var array<int|string> */
    private array $currentPath = [];

    /** @var array<Issue> */
    private array $issues = [];

    public function __construct(
        public readonly ExecutionMode $mode = ExecutionMode::SERIALIZE,
        public readonly bool          $allowPartialFailures = false,
        public readonly bool          $validateOnSerialize = true,
        public readonly mixed         $userProvidedContext = null,
    )
    {
    }

    public function shouldRunValidators(): bool
    {
        return $this->mode === ExecutionMode::PARSE
            ? true
            : $this->validateOnSerialize;
    }

    /**
     * @internal
     */
    public function cloneForProbing(): Context
    {
        $clone = clone $this;
        $clone->issues = [];
        return $clone;
    }

    public function mergeProbingIssues(Context $context): void
    {
        array_push($this->issues, ...$context->issues);
    }

    /** @api */
    public function addIssue(Issue $issue): void
    {
        $this->issues[] = $issue->setBasePath($this->currentPath);
    }

    /** @api */
    public function addIssues(Issue ...$issues): void
    {
        foreach ($this->issues as $issue) {
            $this->addIssue($issue);
        }
    }

    /**
     * @return array<Issue>
     * @api
     */
    public function getIssues(): array
    {
        return $this->issues;
    }

    /** @api */
    public function hasIssues(): bool
    {
        return count($this->issues) > 0;
    }

    /** @internal */
    public function enter(string|int $path): void
    {
        $this->currentPath[] = $path;
    }

    /** @internal */
    public function leave(): void
    {
        array_pop($this->currentPath);
    }

}
