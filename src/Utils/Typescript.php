<?php declare(strict_types=1);

namespace TypescriptSchema\Utils;

use UnitEnum;

final class Typescript
{
    private const string NEVER = 'never';

    public static function literal(string|int|float|bool|null $value): string
    {
        return match (gettype($value)) {
            'integer', 'double' => (string) $value,
            'boolean' => self::boolToStringLiteral($value),
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

    public static function enumValueString(\UnitEnum $enum): string
    {
        return $enum instanceof \BackedEnum
            ? self::literal($enum->value)
            : self::NEVER;
    }

    private static function boolToStringLiteral(bool $value): string
    {
        return $value ? 'true' : 'false';
    }

    private static function wrapInSingleQuote(string $input): string
    {
        $stringSafeInput = addslashes($input);
        return "'{$stringSafeInput}'";
    }
}