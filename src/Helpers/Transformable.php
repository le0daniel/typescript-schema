<?php declare(strict_types=1);

namespace TypescriptSchema\Helpers;

use Closure;
use TypescriptSchema\TransformWrapper;

trait Transformable
{
    public function transform(Closure $transformer, string|Closure|null $outputType = null): TransformWrapper
    {
        return TransformWrapper::make($this, $transformer, $outputType);
    }

}