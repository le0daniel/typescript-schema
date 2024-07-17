<?php declare(strict_types=1);

namespace TypescriptSchema\Utils;

use Closure;
use stdClass;

final class Serialize
{

    public static function safeType(mixed $type): string
    {
        return match (gettype($type)) {
            'string' => "string<'{$type}'>",
            'integer' => "int<{$type}>",
            'double' => "float<{$type}>",
            'object' => self::safePrintObject($type),
            'boolean' => self::safePrintBoolean($type),
            'array' => 'array',
            'NULL' => 'NULL',
            default => 'unknown',
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

        if ($object instanceof Closure) {
            return 'closure';
        }

        $parts = explode('\\', $object::class);
        $basename = end($parts);

        if ($object instanceof \UnitEnum) {
            return "enum<{$basename}::{$object->name}>";
        }

        return "object<{$basename}>";
    }

}