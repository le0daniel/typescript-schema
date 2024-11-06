<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Helpers\Context;

interface Type
{
    public function toDefinition(): SchemaDefinition;

    /**
     * Returns any data on success (including NULL), `Value::INVALID` on failure.
     *
     * **Important**: This method should not throw any error. Any error thrown here
     * might result in a fatal error and any further parsing is prevented.
     *
     * The value is assumed to be used in PHP. If the value does need special serialization
     * implement the SerializesOutputValue interface. This will guarantee that the value is
     * serialized correctly for json.
     *
     * @param mixed|Value $value
     * @param Context $context
     * @return mixed
     * @internal
     */
    public function resolve(mixed $value, Context $context): mixed;
}
