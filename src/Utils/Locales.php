<?php declare(strict_types=1);

namespace TypescriptSchema\Utils;

use RuntimeException;

final class Locales
{

    /**
     * @throws RuntimeException
     * @return array{0: string, 1: string|null}
     */
    public static function explodeIntoLanguageAndCountry(string $locale): array
    {
        if (!self::isValidLocaleString($locale)) {
            throw new RuntimeException("Invalid locale given: {$locale}");
        }

        return strlen($locale) === 2
            ? [$locale, null]
            : [substr($locale, 0, 2,), strtoupper(substr($locale, 3, 2))];
    }

    public static function isValidLocaleString(string $locale): bool
    {
        return preg_match('/^[a-z]{2}([_\-][a-zA-Z]{2})?$/', $locale) === 1;
    }

    /**
     * @throws RuntimeException
     */
    public static function normalizeLocaleString(string $locale): string
    {
        [$lang, $country] = self::explodeIntoLanguageAndCountry($locale);
        return isset($country) ? "{$lang}_{$country}" : $lang;
    }

}