<?php declare(strict_types=1);

namespace TypescriptSchema\Utils;

final class Locales
{

    public static function explodeIntoLanguageAndCountry(string $locale): array
    {
        if (preg_match('/^[a-z]{2}([_\-][a-zA-Z]{2})?$/', $locale) !== 1) {
            throw new \RuntimeException("Invalid locale given: {$locale}");
        }

        return strlen($locale) === 2
            ? [$locale, null]
            : [substr($locale, 0, 2,), strtoupper(substr($locale, 3, 2))];
    }

}