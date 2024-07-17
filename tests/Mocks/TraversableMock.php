<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Mocks;

final class TraversableMock implements \Iterator
{
    private readonly array $data;
    private int $currentIndex = 0;

    public function __construct(array $data)
    {
        $items = [];
        foreach ($data as $key => $value) {
            $items[] = [$key, $value];
        }
        $this->data = $items;
    }

    public function current(): mixed
    {
        return $this->data[$this->currentIndex][1];
    }

    public function next(): void
    {
        $this->currentIndex++;
    }

    public function key(): int|string
    {
        return $this->data[$this->currentIndex][0];
    }

    public function valid(): bool
    {
        return $this->currentIndex < count($this->data);
    }

    public function rewind(): void
    {
        $this->currentIndex = 0;
    }
}