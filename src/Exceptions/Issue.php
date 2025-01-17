<?php declare(strict_types=1);

namespace TypescriptSchema\Exceptions;

use Exception;
use Throwable;
use TypescriptSchema\Data\Enum\IssueType;
use TypescriptSchema\Utils\Serialize;

/**
 * @phpstan-type Path array<int|string>
 */
final class Issue extends Exception
{
    /** @var Path  */
    private array $basePath = [];

    private bool $isFatal = false;

    /**
     * @param IssueType $type
     * @param string $message
     * @param array<string, mixed> $metadata
     * @param Path $path
     * @param ?Throwable|null $previous
     * @param string|null $localizationKey
     */
    protected function __construct(
        public readonly IssueType $type,
        string                    $message,
        public readonly array     $metadata = [],
        protected readonly array  $path = [],
        ?Throwable                 $previous = null,
        private readonly ?string  $localizationKey = null,
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
     * @param Path $path
     * @internal
     */
    public function setBasePath(array $path): Issue
    {
        $this->basePath = $path;
        return $this;
    }

    /**
     * @param Path $path
     */
    public static function coercionFailure(string $expected, mixed $actual, array $path = []): Issue
    {
        $actual = Serialize::safeType($actual);
        return new self(
            IssueType::COERCION_FAILURE,
            "Failed to coerce value to {$expected}, got {$actual}",
            ['expected' => $expected, 'actual' => $actual],
            $path,
            localizationKey: 'coercion_failure',
        );
    }

    /**
     * @param Path $path
     */
    public static function invalidType(string $expected, mixed $actual, array $path = []): Issue
    {
        $actual = Serialize::safeType($actual);
        return new self(
            IssueType::INVALID_TYPE,
            "Expected {$expected}, got {$actual}",
            ['expected' => $expected, 'actual' => $actual],
            $path,
            localizationKey: 'invalid_type',
        );
    }

    /**
     * @param Path $path
     */
    public static function invalidKey(string $expected, mixed $actual, array $path = []): Issue
    {
        $actual = Serialize::safeType($actual);
        return new self(
            IssueType::INVALID_KEY,
            "Expected keys to be of {$expected}, got {$actual}",
            ['expected' => $expected, 'actual' => $actual],
            $path,
            localizationKey: 'invalid_key',
        );
    }

    /**
     * @param array<mixed> $data
     * @param Path $path
     */
    public static function custom(string $message, array $data = [], array $path = [], ?string $localizationKey = null): Issue
    {
        return new self(
            IssueType::CUSTOM,
            $message,
            $data,
            $path,
            localizationKey: $localizationKey,
        );
    }

    /**
     * @param array<mixed> $data
     * @param Path $path
     */
    public static function generic(array $data = [], array $path = []): Issue
    {
        return new self(
            IssueType::CUSTOM,
            "Invalid data provided.",
            $data,
            $path,
            localizationKey: 'generic_failure',
        );
    }

    /**
     * @internal
     */
    public static function captureThrowable(Throwable $throwable): Issue
    {
        if ($throwable instanceof Issue) {
            return $throwable;
        }

        $issue = new self(
            IssueType::INTERNAL_ERROR,
            'Internal error',
            previous: $throwable,
            localizationKey: 'internal_error',
        );
        $issue->isFatal = true;
        return $issue;
    }

    /**
     * @return Path
     */
    public function getPath(): array
    {
        return [
            ... $this->basePath,
            ... $this->path,
        ];
    }

    public function getLocalizationKey(): string
    {
        return $this->localizationKey ?? $this->getMessage();
    }

    public function pathAsString(): string
    {
        return implode('.', $this->getPath());
    }
}