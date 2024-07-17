<?php declare(strict_types=1);

namespace TypescriptSchema\Helpers;

use TypescriptSchema\Exceptions\Issue;

class Context
{
    public function __construct(
        public readonly bool $allowPartialFailures = false,
        private array        $currentPath = [],
        private array        $issues = [],
    )
    {
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

    public function issueCount(): int
    {
        return count($this->issues);
    }

    public function mergeProbingIssues(Context $context): void
    {
        array_push($this->issues, ...$context->issues);
    }

    public function addIssue(Issue $issue): void
    {
        $this->issues[] = $issue->setBasePath($this->currentPath);
    }

    /**
     * @return array<Issue>
     */
    public function getIssues(): array
    {
        return $this->issues;
    }

    public function hasIssues(): bool
    {
        return count($this->issues) > 0;
    }

    public function enter(string|int $path): void
    {
        $this->currentPath[] = $path;
    }

    public function leave(): void
    {
        array_pop($this->currentPath);
    }

}
