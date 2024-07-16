<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use Throwable;
use TypescriptSchema\Contracts\Type;
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
        if (!is_iterable($value)) {
            throw Issue::invalidType('iterable', $value);
        }

        $index = 0;
        $parsed = [];
        foreach ($value as $item) {
            $context->enter($index);
            try {
                $value = $this->type->execute($item, $context);
                if ($value === Value::INVALID) {
                    // Issues have been collected further down already.
                    return Value::INVALID;
                }

                $parsed[] = $value;
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

    protected function toDefinition(): string
    {
        return "Array<{$this->type->toDefinition()}>";
    }
}
