<?php declare(strict_types=1);

namespace TypescriptSchema\Data\Enum;

enum Value
{
    case INVALID;
    case UNDEFINED;

    public static function invalidToNull(mixed $value): mixed
    {
        return $value === self::INVALID ? null : $value;
    }

    public static function undefinedToNull(mixed $value): mixed
    {
        return $value === self::UNDEFINED ? null : $value;
    }

    public static function coerceToNull(mixed $value): mixed
    {
        return $value instanceof Value ? null : $value;
    }
}