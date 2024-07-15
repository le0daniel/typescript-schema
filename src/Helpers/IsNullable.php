<?php declare(strict_types=1);

namespace TypescriptSchema\Helpers;

use TypescriptSchema\Complex\NullableWrapper;

trait IsNullable
{
    /**
     * @return $this
     */
    public function nullable(): mixed {
        return NullableWrapper::make($this);
    }
}
