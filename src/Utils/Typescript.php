<?php declare(strict_types=1);

namespace TypescriptSchema\Utils;

use UnitEnum;

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

    public static function doc(array $lines): string
    {
        $linesAsStrings = implode(PHP_EOL . ' * ', $lines);
        return <<<DOCBLOCK
/**
 * {$linesAsStrings}
 */
DOCBLOCK;

    }

    public static function enumString(UnitEnum $enum): string
    {
        return self::wrapInSingleQuote($enum->name);
    }

    public static function enumValueString(\BackedEnum $enum): string
    {
        return self::literal($enum->value);
    }

    private static function bool(bool $value): string
    {
        return $value ? 'true' : 'false';
    }
}