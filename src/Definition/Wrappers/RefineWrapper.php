<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Wrappers;

use Closure;
use Throwable;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Shared\IsNullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\ClosureValidator;
use TypescriptSchema\Helpers\Context;

final class RefineWrapper extends WrapsType
{
    use Transformable, IsNullable;

    /**
     * @var array<ClosureValidator>
     */
    private array $refiners;

    protected function __construct(
        Type                                 $type,
        Closure             $refiner,
        string|Closure|null $message
    )
    {
        parent::__construct($type);
        $this->refiners = [new ClosureValidator($refiner, $message)];
    }

    public static function make(Type $type, Closure $refiner, string|Closure|null $message = null): self
    {
        return new self($type, $refiner, $message);
    }

    public function refine(Closure $refine, string|Closure|null $message = null): RefineWrapper
    {
        $instance = clone $this;
        $instance->refiners[] = new ClosureValidator($refine, $message);
        return $instance;
    }

    public function runRefiners(mixed $value, Context $context): bool
    {
        $isDirty = false;
        foreach ($this->refiners as $validator) {
            try {
                if ($validator->validate($value)) {
                    continue;
                }

                $issue = $validator->produceIssue($value);
            } catch (Throwable $exception) {
                $issue = Issue::captureThrowable($exception);
            }

            $isDirty = true;
            $context->addIssue($issue);
            if ($issue->isFatal()) {
                return false;
            }
        }

        return !$isDirty;
    }

    public function execute(mixed $value, Context $context): mixed
    {
        $resolvedValue = $this->type->execute($value, $context);

        if ($resolvedValue === Value::INVALID) {
            return Value::INVALID;
        }

        return $this->runRefiners($value, $context)
            ? $resolvedValue
            : Value::INVALID;
    }

    public function toInputDefinition(): string
    {
        return $this->type->toInputDefinition();
    }

    public function toOutputDefinition(): string
    {
        return $this->type->toOutputDefinition();
    }

    protected function verifyType(Type $type): void
    {
        return;
    }
}