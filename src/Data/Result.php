<?php declare(strict_types=1);

namespace TypescriptSchema\Data;

use RuntimeException;
use Throwable;
use TypescriptSchema\Data\Enum\Status;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Exceptions\ParsingException;
use TypescriptSchema\Utils\Issues;
use TypescriptSchema\Utils\ObjectCaster;

final class Result
{
    use HasLocalizer;

    public readonly Status $status;

    /**
     * @param mixed $data
     * @param array<Issue> $issues
     */
    public function __construct(
        private readonly mixed $data,
        public readonly array $issues,
    )
    {
        if ($this->data === Value::UNDEFINED) {
            throw new RuntimeException("Unexpectedly got value undefined.");
        }

        $this->status = match (true) {
            $this->data === Value::INVALID => Status::FAILURE,
            empty($this->issues) => Status::SUCCESS,
            !empty($this->issues) => Status::PARTIAL,
        };
    }

    public function toThrowable(): ParsingException
    {
        if (empty($this->issues)) {
            throw new RuntimeException("Can not create throwable without any issues.");
        }

        return (new ParsingException($this->issues))
            ->setLocale($this->locale)
            ->setLocalizer($this->localizer);
    }

    public function getData(): mixed
    {
        return $this->data instanceof Value
            ? null
            : $this->data;
    }

    /**
     * @template T
     * @param class-string<T> $className
     * @return T|null
     */
    public function castInto(string $className): mixed
    {
        if (!$this->isSuccess()) {
            throw new RuntimeException("Can not cast the data in case of failure.");
        }

        // By default, it is optional. So if no value is provided, it is not cast.
        if ($this->data === null) {
            return null;
        }

        try {
            return ObjectCaster::cast($className, $this->data);
        } catch (Throwable $exception) {
            throw new RuntimeException("Failed to cast the data into {$className}.", previous: $exception);
        }
    }

    /**
     * @param bool $debug
     * @return array<int, array{path: array<int, int|string>, message: string, exception?: mixed}>
     */
    public function serializeIssues(bool $debug = false): array
    {
        return Issues::serialize($this->issues, $this->getLocalizer(), $this->locale, $debug);
    }

    public function isSuccess(): bool
    {
        return $this->status === Status::SUCCESS;
    }

    public function isPartial(): bool
    {
        return $this->status === Status::PARTIAL;
    }

    public function isFailure(): bool
    {
        return $this->status === Status::FAILURE;
    }
}