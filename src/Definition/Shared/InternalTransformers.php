<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Shared;

use Closure;

/**
 * This trait is internal and should not be reused.
 * @internal
 */
trait InternalTransformers
{
    /** @var array<Closure(mixed): mixed>  */
    private array $internalTransformers = [];
    private ?string $overwrittenOutputType = null;

    /**
     * Use internal transformers to manipulate a validated value.
     * It is possible to change the type of the return value when an output type is given.
     *
     * @param Closure $transformer
     * @param string|null $outputType
     * @return $this
     */
    protected function addInternalTransformer(Closure $transformer, null|string $outputType = null): static
    {
        $instance = clone $this;
        $instance->internalTransformers[] = $transformer;
        if ($outputType) {
            $instance->overwrittenOutputType = $outputType;
        }
        return $instance;
    }

    protected function runInternalTransformers(mixed $value): mixed
    {
        return array_reduce(
            $this->internalTransformers,
            fn(mixed $value, Closure $transformer) => $transformer($value),
            $value
        );
    }
}
