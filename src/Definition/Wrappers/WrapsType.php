<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Wrappers;

use RuntimeException;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Definition\Schema;


abstract class WrapsType implements Type
{
    private const int RESOLVING_MAX_DEPTH = 10;

    protected function __construct(protected Type $type)
    {
        $this->verifyType($this->type);
    }

    public function toSchema(): Schema
    {
        return new Schema($this);
    }

    /**
     * Overwrite to do additional checks on the type
     * @param Type $type
     * @return void
     */
    abstract protected function verifyType(Type $type): void;

    public function unwrap(): Type
    {
        return $this->type;
    }

    /**
     * @template T of WrapsType
     * @param class-string<T> $className
     * @return bool
     */
    public function containsWrapped(string $className): bool
    {
        $currentDepth = 0;
        $type = $this->unwrap();

        while ($type instanceof WrapsType) {
            if ($currentDepth > self::RESOLVING_MAX_DEPTH) {
                throw new RuntimeException("Too many unwraps called");
            }

            if ($type instanceof $className) {
                return true;
            }

            $type = $type->unwrap();
            $currentDepth++;
        }

        return false;
    }

    public static function mostInnerType(Type $type): Type
    {
        $currentDepth = 0;

        if (!$type instanceof WrapsType) {
            return $type;
        }

        $type = $type->unwrap();

        while ($type instanceof WrapsType) {
            if ($currentDepth > self::RESOLVING_MAX_DEPTH) {
                throw new RuntimeException("Too many unwraps called");
            }

            $type = $type->unwrap();
            $currentDepth++;
        }

        return $type;
    }
}
