<?php declare(strict_types=1);

namespace TypescriptSchema;

use Closure;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Definition\Complex\ArrayType;
use TypescriptSchema\Definition\Complex\DiscriminatedUnionType;
use TypescriptSchema\Definition\Complex\ObjectType;
use TypescriptSchema\Definition\Complex\RecordType;
use TypescriptSchema\Definition\Complex\TupleType;
use TypescriptSchema\Definition\Complex\UnionType;
use TypescriptSchema\Definition\Primitives\AnyType;
use TypescriptSchema\Definition\Primitives\BoolType;
use TypescriptSchema\Definition\Primitives\DateTimeType;
use TypescriptSchema\Definition\Primitives\EnumType;
use TypescriptSchema\Definition\Primitives\IntType;
use TypescriptSchema\Definition\Primitives\LiteralType;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Definition\Primitives\UnknownType;
use UnitEnum;

/**
 * Inherit form this class to create your own shortcuts. All types are immutable, so you can reuse them if needed without any issues.
 */
class Schema
{
    public static function any(): AnyType
    {
        return new AnyType();
    }

    public static function unknown(): UnknownType
    {
        return new UnknownType();
    }

    public static function string(): StringType
    {
        return StringType::make();
    }

    public static function int(): IntType
    {
        return IntType::make();
    }

    public static function dateTime(?string $format = null): DateTimeType
    {
        return DateTimeType::make($format);
    }

    public static function bool(): BoolType
    {
        return BoolType::make();
    }

    public static function literal(string|int|float|bool|UnitEnum $value): LiteralType
    {
        return LiteralType::make($value);
    }

    /**
     * @template T of UnitEnum
     * @param class-string<T> $className
     * @return EnumType
     */
    public static function enum(string $className): EnumType
    {
        return EnumType::make($className);
    }

    public static function array(Type $type): ArrayType
    {
        return new ArrayType($type);
    }

    public static function record(Type $ofType): RecordType
    {
        return new RecordType($ofType);
    }

    public static function object(array|Closure $definition): ObjectType
    {
        return ObjectType::make($definition);
    }

    public static function tuple(Type ... $types): TupleType
    {
        return TupleType::make(...$types);
    }

    public static function union(Type... $types): UnionType
    {
        return UnionType::make(...$types);
    }

    public static function discriminatedUnion(string $discriminator, Type ... $types): DiscriminatedUnionType
    {
        return DiscriminatedUnionType::make($discriminator, ...$types);
    }

    public static function literalUnion(array $literals): UnionType
    {
        return UnionType::make(
            ... array_map(fn($literal) => new LiteralType($literal), $literals)
        );
    }

}
