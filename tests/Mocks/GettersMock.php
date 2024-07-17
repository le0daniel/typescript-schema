<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Mocks;

final readonly class GettersMock
{
    public function __construct(private array $attributes)
    {
    }

    public static function standardObject(array $attributes): \stdClass
    {
        $object = new \stdClass();
        foreach ($attributes as $name => $value) {
            $object->{$name} = $value;
        }
        return $object;
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value): never
    {
        throw new \RuntimeException('Not implemented');
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function __unset(string $name): never
    {
        throw new \RuntimeException('Not implemented');
    }
}