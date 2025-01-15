<?php declare(strict_types=1);

namespace TypescriptSchema\Utils;

final class Arrays
{

    public static function anyKeyExists(array $array, array $keys): bool
    {
        if (array_is_list($array)) {
            return false;
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                return true;
            }
        }
        return false;
    }

}