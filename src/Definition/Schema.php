<?php declare(strict_types=1);

namespace TypescriptSchema\Definition;

use DateTimeInterface;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\ExecutionMode;
use TypescriptSchema\Data\Options;
use TypescriptSchema\Data\Result;
use TypescriptSchema\Definition\Complex\ArrayType;
use TypescriptSchema\Definition\Complex\DiscriminatedUnionType;
use TypescriptSchema\Definition\Complex\Field;
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
use TypescriptSchema\Definition\Wrappers\NullableWrapper;
use TypescriptSchema\Exceptions\ParsingException;
use TypescriptSchema\Execution\Executor;
use TypescriptSchema\Helpers\Context;
use UnitEnum;

/**
 * @method static AnyType any()
 * @method static BoolType bool()
 * @method static IntType int()
 * @method static StringType string()
 * @method static LiteralType literal(string|int|float|bool|UnitEnum $literalValue)
 * @method static NumberType number()
 * @method static DateTimeType dateTime(string $format = DateTimeInterface::ATOM)
 * @method static EnumType enum(string $enumClassName)
 *
 * @method static ArrayType array(Type $type)
 * @method static ObjectType object(array|\Closure $definition)
 * @method static UnionType union(array $types)
 * @method static DiscriminatedUnionType discriminatedUnion(string $fieldName, array $objectTypes)
 * @method static TupleType tuple(array $types)
 * @method static RecordType record(Type $type)
 *
 * @method static Field field(Type $type)
 * @method static Field property(Type $type)
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
        'union' => UnionType::class,
        'discriminatedUnion' => DiscriminatedUnionType::class,
        'tuple' => TupleType::class,
        'record' => RecordType::class,

        'field' => Field::class,
        'property' => Field::class,
    ];

    public function __construct(private readonly Type $type)
    {
    }

    /**
     * Sugar syntax for chaining for PHP version < 8.4
     * @param Type $type
     * @return self
     */
    public static function make(Type $type): self
    {
        return new self($type);
    }

    /**
     * @template T of Type
     * @param Type $type
     * @return NullableWrapper<T>
     */
    public static function nullable(Type $type): NullableWrapper
    {
        return NullableWrapper::make($type);
    }

    /**
     * Helper to make a union of literals.
     *
     * @param string|int|float|bool|UnitEnum ...$literals
     * @return UnionType
     */
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

    /** @throws ParsingException */
    public function serializeOrFail(mixed $data, Options $options = new Options()): Result
    {
        $result = $this->run(ExecutionMode::SERIALIZE, $data, $options);

        if ($result->isFailure()) {
            throw $result->toThrowable();
        }

        return $result;
    }

    public function serialize(mixed $data, Options $options = new Options()): Result
    {
        return $this->run(ExecutionMode::SERIALIZE, $data, $options);
    }

    /** @throws ParsingException */
    public function parseOrFail(mixed $data, Options $options = new Options()): Result
    {
        $result = $this->run(ExecutionMode::PARSE, $data, $options);

        if ($result->isFailure()) {
            throw $result->toThrowable();
        }
        return $result;
    }

    public function parse(mixed $data, Options $options = new Options()): Result
    {
        return $this->run(ExecutionMode::PARSE, $data, $options);
    }

    private function run(ExecutionMode $mode, mixed $data, Options $options): Result
    {
        $context =  new Context(
            mode: $mode,
            allowPartialFailures: $options->allowPartialFailures,
            validateOnSerialize: $options->validateWhenSerializing,
        );

        return new Result(
            Executor::execute($this->type, $data, $context),
            $context->getIssues(),
        );
    }

    public function toDefinition(): SchemaDefinition
    {
        return $this->type->toDefinition();
    }
}