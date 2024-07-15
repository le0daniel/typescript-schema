<?php declare(strict_types=1);

namespace TypescriptSchema\Utils;

final class Typescript
{

    public static function wrapInSingleQuote(string $input): string
    {
        $stringSafeInput = addslashes($input);
        return "'{$stringSafeInput}'";
    }

    public static function literal(string|int|float|bool|null $value): string
    {
        return match (gettype($value)) {
            'integer', 'double' => (string) $value,
            'boolean' => self::bool($value),
            'string' => self::wrapInSingleQuote($value),
            default => 'null',
        };
    }

    public static function bool(bool $value): string
    {
        return $value ? 'true' : 'false';
    }

    public static function enumString(\UnitEnum $enum): string
    {
        if ($enum instanceof \BackedEnum) {
            return is_string($enum->value) ? self::wrapInSingleQuote($enum->value) : (string) $enum->value;
        }

        return "'{$enum->name}'";
    }

}