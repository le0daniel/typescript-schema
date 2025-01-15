<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\SerializesOutputValue;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Schema\Definition;
use TypescriptSchema\Definition\Shared\BaseType;
use TypescriptSchema\Definition\Shared\HasDefaultValue;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;
use UnitEnum;

class EnumType implements Type, SerializesOutputValue
{
    /** @use Nullable<EnumType> */
    use Nullable, Refinable, Transformable, HasDefaultValue, BaseType;

    /**
     * @template T of UnitEnum
     * @param class-string<T> $enumClassName
     */
    public function __construct(public readonly string $enumClassName)
    {
    }

    /**
     * @template T of UnitEnum
     * @param class-string<T> $enumClassName
     */
    public static function make(string $enumClassName): EnumType
    {
        return new self($enumClassName);
    }

    private function parseStringValueToEnum(string|UnitEnum $value): UnitEnum
    {
        /** @var UnitEnum $enumClass */
        $enumClass = $this->enumClassName;
        $cases = $enumClass::cases();


        foreach ($cases as $case) {
            if ($case === $value || $case->name === $value) {
                return $case;
            }
        }

        return Value::INVALID;
    }

    public function parse(mixed $value, Context $context): Value|UnitEnum
    {
        $enumValue = $this->parseStringValueToEnum(
            $this->applyDefaultValue($value)
        );
        if ($enumValue === Value::INVALID) {
            $context->addIssue(Issue::invalidType('Enum value', $value));
            return Value::INVALID;
        }

        return $enumValue;
    }

    public function serializeValue(mixed $value, Context $context): Value|string
    {
        if (!$value instanceof UnitEnum) {
            return Value::INVALID;
        }

        return $value->name;
    }

    public function toDefinition(): SchemaDefinition
    {
        $cases = $this->enumClassName::cases();
        return Definition::same([
            'enum' => array_map(fn(UnitEnum $case): string => $case->name, $cases),
        ]);
    }
}
