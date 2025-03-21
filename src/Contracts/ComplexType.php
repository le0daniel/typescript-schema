<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

interface ComplexType
{

    /**
     * Return deeper types
     *
     * @return array<Type>
     */
    public function getTypes(): array;
}