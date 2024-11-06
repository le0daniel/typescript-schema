<?php declare(strict_types=1);

namespace TypescriptSchema\Data;

use RuntimeException;
use TypescriptSchema\Data\Enum\Status;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Exceptions\ParsingException;

final readonly class Result
{
    public Status $status;

    /**
     * @param mixed $data
     * @param array<Issue> $issues
     */
    public function __construct(
        private mixed $data,
        public array  $issues,
    )
    {
        if ($this->data === Value::UNDEFINED) {
            throw new RuntimeException("Got value Undefined.");
        }

        $this->status = match (true) {
            $this->data === Value::INVALID => Status::FAILURE,
            empty($this->issues) => Status::SUCCESS,
            !empty($this->issues) => Status::PARTIAL,
        };
    }

    public function toThrowable(): ParsingException
    {
        return new ParsingException(
            $this->issues
        );
    }

    public function getData(): mixed
    {
        return $this->data instanceof Value
            ? null
            : $this->data;
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