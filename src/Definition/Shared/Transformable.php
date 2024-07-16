<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

use Closure;
use TypescriptSchema\Definition\Wrappers\TransformWrapper;

trait Transformable
{
    public function transform(Closure $transformer, string|Closure|null $outputType = null): TransformWrapper
    {
        return TransformWrapper::make($this, $transformer, $outputType);
    }

}