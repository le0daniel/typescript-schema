<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

use Closure;
use TypescriptSchema\Definition\Complex\TransformType;

trait Transformable
{

    /**
     * @param Closure(mixed): mixed $transformer
     * @return TransformType
     */
    public function transform(Closure $transformer, ?array $outputType = null): TransformType
    {
        return new TransformType($this, $transformer, $outputType);
    }

}