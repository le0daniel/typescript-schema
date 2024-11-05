<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Wrappers;

use DateTimeInterface;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\ExecutionMode;
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
use TypescriptSchema\Definition\Primitives\NumberType;
use TypescriptSchema\Definition\Primitives\StringType;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\Context;
use UnitEnum;

/**
 * @method static AnyType any()
 * @method static BoolType bool()
 * @method static IntType int()
 * @method static NumberType number()
 * @method static StringType string()
 * @method static LiteralType literal(string|int|float|bool|UnitEnum $literalValue)
 * @method static DateTimeType dateTime(string $format = DateTimeInterface::ATOM)
 * @method static EnumType enum(string $enumClassName)
 *
 *
 * @method static ObjectType object(array|\Closure $definition)
 * @method static ArrayType array(Type $type)
 */
final class Schema
{
    private const array TYPES = [
        'any' => AnyType::class,
        'bool' => BoolType::class,
        'int' => IntType::class,
        'string' => StringType::class,
        'literal' => LiteralType::class,
        'number' => NumberType::class,
        'dateTime' => DateTimeType::class,
        'enum' => EnumType::class,

        'array' => ArrayType::class,
        'object' => ObjectType::class,
        'discriminatedUnion' => DiscriminatedUnionType::class,
        'tuple' => TupleType::class,
        'record' => RecordType::class,
    ];

    /**
     * @template T of Type
     * @param T $type
     * @return NullableWrapper<T>
     */
    public static function nullable(Type $type): NullableWrapper
    {
        return NullableWrapper::make($type);
    }

    public function __construct(private readonly Type $type)
    {
    }

    public static function literalUnion(string|int|float|bool|UnitEnum ... $literals): UnionType
    {
        return new UnionType(
            array_map(fn($literal): LiteralType => new LiteralType($literal), $literals)
        );
    }

    public static function __callStatic(string $name, array $arguments)
    {
        $type = self::TYPES[$name] ?? null;
        if (!$name) {
            throw new \RuntimeException("Invalid type $name");
        }
        return new $type(...$arguments);
    }

    public static function make(Type $type): self
    {
        return new self($type);
    }

    public function serialize(mixed $data, array $options = [])
    {
        return Executor::execute($this->type, $data, new Context(mode: ExecutionMode::SERIALIZE));
    }

    public function parse(mixed $data, array $options = [])
    {
        return Executor::execute($this->type, $data, new Context(mode: ExecutionMode::PARSE));
    }

    public function toDefinition(): SchemaDefinition
    {
        return $this->type->toDefinition();
    }
}