<?php declare(strict_types=1);

namespace TypescriptSchema\Data\Enum;

use JsonSerializable;

enum Value implements JsonSerializable
{
    case INVALID;
    case UNDEFINED;

    public static function undefinedToNull(mixed $value): mixed
    {
        return $value === self::UNDEFINED ? null : $value;
    }

    /**
     * To ensure json serialization
     * @return null
     */
    public function jsonSerialize(): null
    {
        return null;
    }
}