<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Mocks;

final class IdTypeMock
{
    public function __construct(
        public readonly int $id
    )
    {
    }
}