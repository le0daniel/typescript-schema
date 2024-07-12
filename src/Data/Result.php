<?php declare(strict_types=1);

namespace TypescriptSchema\Data;

use RuntimeException;

final readonly class Result implements \JsonSerializable
{
    public Status $status;

    public function __construct(
        private mixed $data,
        public array $issues,
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

    public function getData(): mixed
    {
        return Value::invalidToNull($this->data);
    }

    public function isSuccess(): bool
    {
        return $this->status === Status::SUCCESS;
    }

    public function isPartial(): bool
    {
        return $this->status === Status::PARTIAL;
    }

    public function isFailure(): bool {
        return $this->status === Status::FAILURE;
    }

    public function jsonSerialize(): array
    {
        return [
            'data' => $this->getData(),
            'status' => $this->status->name,
            'issues' => $this->issues,
        ];
    }
}