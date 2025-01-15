<?php declare(strict_types=1);

namespace TypescriptSchema\Data\Schema;

use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Utils\Typescript;

final readonly class Definition implements SchemaDefinition
{

    /**
     * @param array<mixed> $input
     * @param array<mixed> $output
     */
    public function __construct(
        public array|JsonSchema $input,
        public array|JsonSchema $output,
    )
    {
    }

    /**
     * @param array<mixed> $definition
     * @return Definition
     */
    public static function same(array $definition): Definition
    {
        return new Definition($definition, $definition);
    }

    /**
     * Schema which applies to parse
     * @return array|mixed[]
     */
    public function input(): array
    {
        return $this->input;
    }

    /**
     * Schema which applies to serialize
     * @return array|mixed[]
     */
    public function output(): array
    {
        return $this->output;
    }
}