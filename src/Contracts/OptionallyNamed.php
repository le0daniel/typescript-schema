<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

interface OptionallyNamed
{
    public function getName(): ?string;
}