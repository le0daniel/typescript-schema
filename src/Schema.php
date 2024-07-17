<?php declare(strict_types=1);

namespace TypescriptSchema;

use Closure;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Definition\Complex\ArrayType;
use TypescriptSchema\Definition\Complex\ObjectType;
use TypescriptSchema\Definition\Complex\RecordType;
use TypescriptSchema\Definition\Complex\TupleType;
use TypescriptSchema\Definition\Complex\UnionType;
use TypescriptSchema\Definition\Primitives\AnyType;
use TypescriptSchema\Definition\Primitives\BoolType;
use TypescriptSchema\Definition\Primitives\IntType;
use TypescriptSchema\Definition\Primitives\LiteralType;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Definition\Primitives\UnknownType;
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

    public static function literal(string|int|float|bool|UnitEnum $value): LiteralType
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
