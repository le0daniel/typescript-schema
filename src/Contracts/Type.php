<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

use TypescriptSchema\Data\Value;
use TypescriptSchema\Helpers\Context;

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

    public function toInputDefinition(): string;
    public function toOutputDefinition(): string;

    // protected function toDefinition(): string;
}
