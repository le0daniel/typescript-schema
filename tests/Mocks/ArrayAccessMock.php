<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Mocks;

final class ArrayAccessMock implements \ArrayAccess
{
    public function __construct(private readonly array $attributes)
    {
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->attributes);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new \RuntimeException('Not implemented');
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new \RuntimeException('Not implemented');
    }
}