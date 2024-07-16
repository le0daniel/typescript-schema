<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

use TypescriptSchema\Definition\Wrappers\NullableWrapper;

trait IsNullable
{
    /**
     * @return $this
     */
    public function nullable(): mixed {
        return NullableWrapper::make($this);
    }
}
