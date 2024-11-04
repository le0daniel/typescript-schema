<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

use TypescriptSchema\Helpers\Context;

interface ComplexType extends Type
{

    public function resolve(mixed $value, Context $context): mixed;

}