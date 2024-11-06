<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Helpers\Context;

interface SerializesOutputValue
{

    /**
     * Serialize the value correctly for later json encoding. This is useful if values
     * are parsed to a specific php object and serialization needs to be done. A good example
     * is the DateTimeType. The value is parsed to a DateTimeImmutable instance and if serializing,
     * it is transformed to a string value with the right format.
     *
     * The value is the return type of resolve. Only valid values are passed in.
     *
     * @param mixed|Value $value
     * @param Context $context
     * @return mixed
     * @internal
     */
    public function serializeValue(mixed $value, Context $context): mixed;

}