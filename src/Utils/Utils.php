<?php declare(strict_types=1);

namespace TypescriptSchema\Utils;

use ArrayAccess;

final class Utils
{

    public static function valueExists(string $key, mixed $arrayOrObject): bool
    {
        if (is_array($arrayOrObject)) {
            return array_key_exists($key, $arrayOrObject);
        }

        if (!is_object($arrayOrObject)) {
            return false;
        }

        if ($arrayOrObject instanceof ArrayAccess) {
            return $arrayOrObject->offsetExists($key);
        }

        if (method_exists($arrayOrObject, '__isset')) {
            return $arrayOrObject->__isset($key);
        }

        return property_exists($arrayOrObject, $key);
    }

    public static function extractValue(string $key, array|object $arrayOrObject): mixed
    {
        if (is_array($arrayOrObject) || $arrayOrObject instanceof ArrayAccess) {
            return $arrayOrObject[$key] ?? null;
        }

        return $arrayOrObject->{$key} ?? null;
    }
}
