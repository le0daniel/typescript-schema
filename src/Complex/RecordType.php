<?php declare(strict_types=1);

namespace TypescriptSchema\Complex;

use Throwable;
use TypescriptSchema\BaseType;
use TypescriptSchema\Context\Context;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\IsNullable;
use TypescriptSchema\Type;

final class RecordType extends BaseType
{
    use IsNullable;

    public function __construct(private readonly Type $ofType)
    {
    }

    public static function make(Type $ofType): self
    {
        return new self($ofType);
    }

    protected function validateAndParseType(mixed $value, Context $context): array|Value
    {
        if (!is_iterable($value)) {
            throw Issue::invalidType('iterable', $value);
        }

        $values = [];
        foreach ($value as $name => $itemValue) {
            $context->enter($name);

            try {
                if (!is_string($name)) {
                    $context->addIssue(Issue::invalidKey("string", $name));
                    return Value::INVALID;
                }
                $value = $this->ofType->execute($itemValue, $context);
                if ($value === Value::INVALID) {
                    return Value::INVALID;
                }
                $values[$name] = $value;
            } catch (Throwable $exception) {
                $context->addIssue(Issue::captureThrowable($exception));
                return Value::INVALID;
            }
            finally {
                $context->leave();
            }
        }

        return $values;
    }

    public function toDefinition(): string
    {
        return "Record<string,{$this->ofType->toDefinition()}>";
    }
}
