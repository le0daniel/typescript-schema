<?php declare(strict_types=1);

namespace TypescriptSchema\Helpers;

use Closure;
use TypescriptSchema\Utils\Locales;
use TypescriptSchema\Utils\Serialize;
use TypescriptSchema\Contracts;

class Localizer implements Contracts\Localizer
{
    /** @var array<string, bool> */
    private array $loadedLocales = [];

    /** @var array<string, array> */
    private array $locales = [];

    private Closure $loader;

    public function __construct(?Closure $loader = null)
    {
        $this->loader = $loader ?? self::defaultLoader(...);
    }

    private static function defaultLoader(string $locale): ?array
    {
        if (file_exists(__DIR__ . "/../Locales/{$locale}.php")) {
            return require __DIR__ . "/../Locales/{$locale}.php";
        }

        return null;
    }

    public static function prepareParameters(array $parameters): array
    {
        return array_map(
            fn(mixed $value): string => is_scalar($value) ? (string)$value : Serialize::safeType($value),
            $parameters
        );
    }

    public function translate(string $locale, string $key, array $parameters = [], ?string $default = null): string
    {
        $translated = $this->findTranslationForKey($locale, $key);
        if (!$translated) {
            return $default ?? $key;
        }

        return $this->replaceParameters($translated, $parameters);
    }

    private function replaceParameters(string $target, array $parameters): string
    {
        $stringSafeParameters = self::prepareParameters($parameters);
        $keys = array_map(fn($key): string => ":{$key}", array_keys($stringSafeParameters));
        return str_replace($keys, array_values($stringSafeParameters), $target);
    }

    private function findTranslationForKey(string $locale, string $key): ?string
    {
        [$language, $country] = Locales::explodeIntoLanguageAndCountry($locale);

        // Load the country locale with priority.
        if ($country) {
            $this->loadLocaleOnce("{$language}_{$country}");
            if (isset($this->locales["{$language}_{$country}"][$key])) {
                return $this->locales["{$language}_{$country}"][$key];
            }
        }

        $this->loadLocaleOnce($language);
        return $this->locales[$language][$key] ?? null;
    }

    private function loadLocaleOnce(string $locale): void
    {
        if (isset($this->loadedLocales[$locale])) {
            return;
        }

        $translations = ($this->loader)($locale);
        $this->loadedLocales[$locale] = true;
        $this->locales[$locale] = $translations;
    }
}