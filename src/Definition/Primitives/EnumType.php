<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use ReflectionEnum;
use TypescriptSchema\Contracts\LeafType;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Shared\Coerce;
use TypescriptSchema\Definition\Shared\InternalTransformers;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;
use TypescriptSchema\Utils\Typescript;
use UnitEnum;

class EnumType implements LeafType
{
    /** @uses Nullable<EnumType> */
    use Nullable;

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

    public function parseAndValidate(mixed $value, Context $context): Value|UnitEnum
    {
        $enumValue = $this->parseStringValueToEnum($value);
        if ($enumValue === Value::INVALID) {
            $context->addIssue(Issue::invalidType('Enum value', $value));
            return Value::INVALID;
        }

        return $enumValue;
    }

    public function validateAndSerialize(mixed $value, Context $context): Value|string
    {
        $enumValue = $this->parseStringValueToEnum($value);
        if ($enumValue === Value::INVALID) {
            $context->addIssue(Issue::invalidType('Enum value', $value));
            return Value::INVALID;
        }
        return $enumValue->name;
    }

    public function toDefinition(): SchemaDefinition
    {
        $cases = $this->enumClassName::cases();
        return Definition::same([
            'enum' => array_map(fn(UnitEnum $case): string => $case->name, $cases),
        ]);
    }
}
