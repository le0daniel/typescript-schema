<?php declare(strict_types=1);

namespace TypescriptSchema\Exceptions;

use Exception;
use JsonSerializable;
use Throwable;
use TypescriptSchema\Data\IssueType;
use TypescriptSchema\Utils\Serialize;

final class Issue extends Exception implements JsonSerializable
{
    private array $basePath = [];

    private bool $isFatal = false;

    protected function __construct(
        public readonly IssueType $type,
        string                    $message,
        public readonly array     $metadata = [],
        public readonly array     $path = [],
        Throwable                 $previous = null
    )
    {
        parent::__construct($message, previous: $previous);
    }

    /**
     * Marks the error as Fatal, execution stops.
     * @return $this
     */
    public function fatal(): static
    {
        $this->isFatal = true;
        return $this;
    }

    /**
     * @internal
     */
    public function isFatal(): bool
    {
        return $this->isFatal;
    }

    /**
     * @internal
     */
    public function setBasePath(array $path): Issue
    {
        $this->basePath = $path;
        return $this;
    }

    public static function coercionFailure(string $expected, mixed $actual, array $path = []): Issue
    {
        $actual = Serialize::safeType($actual);
        return new self(
            IssueType::COERCION_FAILURE,
            "Failed to coerce value to {$expected}, got {$actual}",
            ['expected' => $expected, 'actual' => $actual],
            $path
        );
    }

    public static function invalidType(string $expected, mixed $actual, array $path = []): Issue
    {
        $actual = Serialize::safeType($actual);
        return new self(
            IssueType::INVALID_TYPE,
            "Expected {$expected}, got {$actual}",
            ['expected' => $expected, 'actual' => $actual],
            $path
        );
    }

    public static function invalidKey(string $expected, mixed $actual, array $path = []): Issue
    {
        $actual = Serialize::safeType($actual);
        return new self(
            IssueType::INVALID_KEY,
            "Expected keys to be of {$expected}, got {$actual}",
            ['expected' => $expected, 'actual' => $actual],
            $path
        );
    }

    public static function custom(string $message, array $data = [], array $path = []): Issue
    {
        return new self(
            IssueType::CUSTOM,
            $message,
            $data,
            $path
        );
    }

    public static function generic(array $data = [], array $path = []): Issue
    {
        return new self(
            IssueType::CUSTOM,
            "Invalid data provided.",
            $data,
            $path
        );
    }

    /**
     * @internal
     */
    public static function captureThrowable(Throwable $throwable): Issue
    {
        if ($throwable instanceof self) {
            return $throwable;
        }

        $issue = new self(
            IssueType::INTERNAL_ERROR,
            'Internal error',
            previous: $throwable,
        );
        $issue->isFatal = true;
        return $issue;
    }

    public function jsonSerialize(): array
    {
        return [
            ... $this->metadata,
            'type' => $this->type->jsonSerialize(),
            'message' => $this->getMessage(),
            'path' => [
                ... $this->basePath,
                ... $this->path,
            ],
        ];
    }
}