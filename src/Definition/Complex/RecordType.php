<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use Throwable;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Definition\BaseType;
use TypescriptSchema\Definition\Shared\IsNullable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

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

    protected function toDefinition(): Definition
    {
        return new Definition(
            "Record<string,{$this->ofType->toInputDefinition()}>",
            "Record<string,{$this->ofType->toOutputDefinition()}>",
        );
    }
}
