<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

use Closure;
use TypescriptSchema\Definition\Complex\TransformType;

trait Transformable
{

    /**
     * @param Closure(mixed): mixed $transformer
     * @param array<mixed>|null|Closure(array<mixed>):array<mixed> $outputType
     * @return TransformType
     */
    public function transform(Closure $transformer, null|array|Closure $outputType = null): TransformType
    {
        return new TransformType($this, $transformer, $outputType);
    }

}