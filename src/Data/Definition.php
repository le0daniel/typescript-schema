<?php declare(strict_types=1);

namespace TypescriptSchema\Data;

final readonly class Definition
{

    public function __construct(
        public readonly string $input,
        public readonly string $output,
    )
    {
    }

    public static function same(string $definition): self
    {
        return new self($definition, $definition);
    }

}