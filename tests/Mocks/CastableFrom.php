<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Mocks;

final class CastableFrom
{

    public function __construct(
        public readonly string $otherName,
    )
    {
    }

    public static function from(array $data): CastableFrom
    {
        return new self($data['name'] ?? $data['otherName']);
    }

}