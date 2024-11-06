<?php declare(strict_types=1);

namespace TypescriptSchema\Data\Schema;

use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Utils\Typescript;

final readonly class Definition implements SchemaDefinition
{

    public function __construct(
        public array $input,
        public array $output,
    )
    {
    }

    public static function same(array $definition): Definition
    {
        return new Definition($definition, $definition);
    }

    public function input(): array
    {
        return $this->input;
    }

    public function output(): array
    {
        return $this->output;
    }

    public function toTypescriptInput(): string
    {
        return Typescript::fromJsonSchema($this->input);
    }

    public function toTypescriptOutput(): string
    {
        return Typescript::fromJsonSchema($this->output);
    }
}