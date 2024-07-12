<?php declare(strict_types=1);

namespace TypescriptSchema;

use TypescriptSchema\Context\Context;
use TypescriptSchema\Helpers\WrapsType;

final class TransformsResultType extends WrapsType implements OutputType
{
    public static function make(Type $wrappedType)
    {

    }

    public function execute(mixed $value, Context $context): mixed
    {

    }

    public function toDefinition(): string
    {

    }

    /**
     * Can be wrapped by itself.
     * @param Type $type
     * @return void
     */
    protected function verifyType(Type $type): void
    {
    }
}