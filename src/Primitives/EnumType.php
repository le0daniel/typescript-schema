<?php declare(strict_types=1);

namespace TypescriptSchema\Primitives;

use BackedEnum;
use ReflectionEnum;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Utils\Typescript;
use UnitEnum;

class EnumType extends PrimitiveType
{
    /**
     * @template T of UnitEnum
     * @param class-string<T> $enumClassName
     */
    public function __construct(public readonly string $enumClassName)
    {
    }

    public function asString(): static
    {
        return $this->addInternalTransformer(function (UnitEnum $enum): string {
            return $enum->name;
        }, implode('|', array_map(Typescript::enumString(...), $this->enumClassName::cases())));
    }

    /**
     * @template T of UnitEnum
     * @param class-string<T>|null $enumClassName
     * @return EnumType
     */
    public static function make(?string $enumClassName = null): static
    {
        return new self($enumClassName);
    }

    /**
     * @throws \ReflectionException
     */
    protected function toDefinition(): string|Definition
    {
        $inputDefinition = implode('|', array_map(Typescript::enumString(...), $this->enumClassName::cases()));

        $reflection = new ReflectionEnum($this->enumClassName);
        if (!$reflection->isBacked()) {
            // Unit enums fail to serialize to json, so the output type is never.
            return new Definition($inputDefinition, 'never');
        }

        // As backed enums are serialized to values in PHP, by default, we add the types as
        // a union of literal values.
        return new Definition(
            $inputDefinition,
            implode('|', array_map(Typescript::enumValueString(...), $this->enumClassName::cases())),
        );
    }

    protected function parsePrimitiveType(mixed $value): mixed
    {
        if ($value instanceof $this->enumClassName) {
            return $value;
        }

        if (!is_string($value)) {
            throw Issue::invalidType('enum-string', $value);
        }

        foreach ($this->enumClassName::cases() as $enumClass) {
            if ($value === $enumClass->name) {
                return $enumClass;
            }
        }

        throw Issue::invalidType('enum-string', $value);
    }

    protected function coerceValue(mixed $value): mixed
    {
        if (!is_string($value) && !is_int($value)) {
            return $value;
        }

        $reflection = new ReflectionEnum($this->enumClassName);
        if (!$reflection->isBacked()) {
            return $value;
        }

        return $this->enumClassName::tryFrom($value) ?? $value;
    }
}
