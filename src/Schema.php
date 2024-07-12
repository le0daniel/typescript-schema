<?php declare(strict_types=1);

namespace TypescriptSchema;

use TypescriptSchema\Complex\ArrayType;
use TypescriptSchema\Complex\ObjectType;
use TypescriptSchema\Complex\RecordType;
use TypescriptSchema\Complex\TupleType;
use TypescriptSchema\Complex\UnionType;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Primitives\AnyType;
use TypescriptSchema\Primitives\BoolType;
use TypescriptSchema\Primitives\IntType;
use TypescriptSchema\Primitives\LiteralType;
use TypescriptSchema\Primitives\StringType;
use TypescriptSchema\Primitives\UnknownType;
use Closure;
use stdClass;
use UnitEnum;

final class Schema
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

    public static function union(Type... $types): UnionType
    {
        return UnionType::make(...$types);
    }

    public static function literal(string|int|UnitEnum $value): LiteralType
    {
        return LiteralType::make($value);
    }

    public static function listOf(Type $type): ArrayType
    {
        return new ArrayType($type);
    }

    public static function array(Type $type): ArrayType
    {
        return new ArrayType($type);
    }

    public static function collection(Type $type): ArrayType
    {
        return new ArrayType($type);
    }

    public static function int(): IntType
    {
        return IntType::make();
    }

    public static function bool(): BoolType
    {
        return BoolType::make();
    }

    public static function dict(Type $ofType): RecordType
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

    public static function literalUnion(array $literals): UnionType
    {
        return UnionType::make(
            ... array_map(fn($literal) => new LiteralType($literal), $literals)
        );
    }

}
