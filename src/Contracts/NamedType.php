<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

interface NamedType
{
    public function getName(): string;
}