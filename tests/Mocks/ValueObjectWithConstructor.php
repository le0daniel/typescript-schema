<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Mocks;

final readonly class ValueObjectWithConstructor
{
    public function __construct(
        public string $name,
    )
    {
    }
}