<?php declare(strict_types=1);

namespace TypescriptSchema\Utils;

use stdClass;

final class Serialize
{

    public static function safeType(mixed $type): string
    {
        return match (gettype($type)) {
            'string' => "string<'{$type}'>",
            'integer' => "int<{$type}>",
            'object' => self::safePrintObject($type),
            'boolean' => self::safePrintBoolean($type),
            default => gettype($type),
        };
    }

    private static function safePrintBoolean(bool $value): string
    {
        $boolString = $value ? 'true' : 'false';
        return "bool<{$boolString}>";
    }

    private static function safePrintObject(object $object): string
    {
        if ($object instanceof stdClass) {
            return 'object';
        }

        $className = get_class($object);
        return "object<{$className}>";
    }

}