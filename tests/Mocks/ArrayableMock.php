<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Mocks;

final class ArrayableMock
{
    public function __construct(private readonly array $data)
    {
    }

    public function toArray(): array
    {
        return $this->data;
    }

}