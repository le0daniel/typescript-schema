<?php declare(strict_types=1);

namespace TypescriptSchema\Exceptions;

use Exception;
use JsonSerializable;
use Stringable;
use Throwable;
use TypescriptSchema\Data\Enum\IssueType;
use TypescriptSchema\Data\Enum\SerializationMode;
use TypescriptSchema\Utils\Serialize;

final class Issue extends Exception implements JsonSerializable, Stringable
{
    private const array REMOVE_METADATA_IN_LIMITED_MODE = ['actual'];

    private array $basePath = [];

    private bool $isFatal = false;

    protected function __construct(
        public readonly IssueType $type,
        string                    $message,
        public readonly array     $metadata = [],
        protected readonly array  $path = [],
        Throwable                 $previous = null
    )
    {
        parent::__construct($message, previous: $previous);
    }

    /**
     * Signifies that an internal error occurred. If so, a previous exception is available, which you might want to report.
     *
     * @return bool
     */
    public function isInternal(): bool
    {
        return $this->type === IssueType::INTERNAL_ERROR;
    }

    /**
     * Marks the error as Fatal, execution stops.
     * @return $this
     */
    public function fatal(): self
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

    public function getPath(): array
    {
        return [
            ... $this->basePath,
            ... $this->path,
        ];
    }

    public function toArray($mode = SerializationMode::LIMITED): array
    {
        $metadata = $this->metadata;
        if ($mode === SerializationMode::LIMITED) {
            foreach (self::REMOVE_METADATA_IN_LIMITED_MODE as $key) {
                unset($metadata[$key]);
            }
        }

        $data = [
            ... $metadata,
            'type' => $this->type->jsonSerialize(),
            'message' => $this->getMessage(),
            'path' => $this->getPath(),
        ];

        if ($mode === SerializationMode::ALL_WITH_DEBUG && $this->getPrevious()) {
            $data['previous'] = [
                'message' => $this->getPrevious()->getMessage(),
                'file' => $this->getPrevious()->getFile(),
                'line' => $this->getPrevious()->getLine(),
                'trace' => $this->getPrevious()->getTrace(),
            ];
        }

        return $data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return $this->message;
    }
}