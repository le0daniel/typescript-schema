<?php declare(strict_types=1);

namespace TypescriptSchema\Helpers;

use TypescriptSchema\Context\Context;
use TypescriptSchema\Type;
use RuntimeException;


abstract class WrapsType implements Type
{
    use ParsesInput;

    final protected function __construct(protected Type $type)
    {
        $this->verifyType($this->type);
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

    public function mostInnerType(): Type
    {
        $depth = 0;
        $type = $this->unwrap();

        while ($type instanceof WrapsType) {
            if ($depth > 4) {
                throw new RuntimeException("Too many unwraps called");
            }

            $type = $type->unwrap();
            $depth++;
        }

        return $type;
    }

    public function execute(mixed $value, Context $context): mixed
    {
        return $this->type->execute($value, $context);
    }

    public function toDefinition(): string
    {
        return $this->type->toDefinition();
    }
}
