<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Definition\Wrappers\NullableWrapper;

/**
 * @template T of Type
 */
trait Nullable
{

    /**
     * @return NullableWrapper<T>
     */
    public function nullable(): NullableWrapper
    {
        return NullableWrapper::make($this);
    }

}