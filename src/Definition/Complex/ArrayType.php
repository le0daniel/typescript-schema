<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use Closure;
use Generator;
use Throwable;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\BaseType;
use TypescriptSchema\Definition\Shared\IsNullable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

final class ArrayType extends BaseType
{
    use IsNullable;

    public function __construct(
        private readonly Type $type,
    )
    {
    }

    public static function make(Type $type): self
    {
        return new self($type);
    }

    protected function validateAndParseType(mixed $value, Context $context): array|Value
    {
        $value = match (true) {
            is_iterable($value), $value instanceof Generator => $value,
            $value instanceof Closure => $value(),
            is_object($value) && method_exists($value, 'toArray') => $value->toArray(),
            default => throw Issue::invalidType('iterable', $value),
        };

        if (!is_iterable($value) && !$value instanceof Generator) {
            throw Issue::invalidType('iterable', $value);
        }

        $index = 0;
        $parsed = [];
        foreach ($value as $item) {
            $context->enter($index);
            try {
                $itemValue = $this->type->execute($item, $context);
                if ($itemValue === Value::INVALID) {
                    // Issues have been collected further down already.
                    return Value::INVALID;
                }

                $parsed[] = $itemValue;
                $index++;
            } catch (Throwable $exception) {
                $context->addIssue(Issue::captureThrowable($exception));
                return Value::INVALID;
            }
            finally {
                $context->leave();
            }
        }
        return $parsed;
    }

    public function toDefinition(): Definition
    {
        return $this->type
            ->toDefinition()
            ->wrap(fn(string $definition) => "Array<{$definition}>");
    }
}
