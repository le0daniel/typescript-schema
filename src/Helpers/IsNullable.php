<?php declare(strict_types=1);

namespace TypescriptSchema\Helpers;

use TypescriptSchema\Complex\NullableType;

trait IsNullable
{
    /**
     * @return $this
     */
    public function nullable(): mixed {
        return NullableType::make($this);
    }
}
