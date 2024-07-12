<?php declare(strict_types=1);

namespace TypescriptSchema;

use TypescriptSchema\Context\Context;
use TypescriptSchema\Data\Value;

interface Type
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
    public function execute(mixed $value, Context $context): mixed;

    public function toDefinition(): string;
}
