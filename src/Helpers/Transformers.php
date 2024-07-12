<?php declare(strict_types=1);

namespace TypescriptSchema\Helpers;

use Closure;

trait Transformers
{
    protected array $transformers = [];

    /**
     * Transformers should not change the **output type**. For this, Transform is used.
     * @param Closure $transformer
     * @return $this
     */
    protected function addTransformer(Closure $transformer): static
    {
        $instance = clone $this;
        $instance->transformers[] = $transformer;
        return $instance;
    }

    protected function runTransformers(mixed $value): mixed
    {
        return array_reduce(
            $this->transformers,
            fn(mixed $value, Closure $transformer) => $transformer($value),
            $value
        );
    }
}
