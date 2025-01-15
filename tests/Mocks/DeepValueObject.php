<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Mocks;

final readonly class DeepValueObject
{
    public function __construct(
        public ?ValueObjectWithConstructor    $with,
        public ?ValueObjectWithoutConstructor $without,
    )
    {
    }
}