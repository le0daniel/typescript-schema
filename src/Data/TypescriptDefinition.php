<?php declare(strict_types=1);

namespace TypescriptSchema\Data;

final readonly class TypescriptDefinition
{

    public function __construct(
        public readonly string $input,
        public readonly string $output,
    )
    {
    }

}