<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Helpers\Context;

interface LeafType extends Type
{

    /**
     * Returns any data on success (including NULL), `Value::INVALID` on failure.
     *
     * **Important**: This method should not throw any error. Any error thrown here
     * might result in a fatal error and any further parsing is prevented.
     *
     * @param mixed|Value $value
     * @param Context $context
     * @return mixed
     * @internal
     */
    public function parseAndValidate(mixed $value, Context $context): mixed;

    /**
     * Returns any data on success (including NULL), `Value::INVALID` on failure.
     *
     * **Important**: This method should not throw any error. Any error thrown here
     * might result in a fatal error and any further parsing is prevented.
     *
     * @param mixed|Value $value
     * @param Context $context
     * @return mixed
     * @internal
     */
    public function validateAndSerialize(mixed $value, Context $context): mixed;

}