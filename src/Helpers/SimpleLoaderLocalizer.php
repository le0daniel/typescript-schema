<?php declare(strict_types=1);

namespace TypescriptSchema\Helpers;

use Closure;
use TypescriptSchema\Utils\Locales;
use TypescriptSchema\Utils\Serialize;
use TypescriptSchema\Contracts;

class SimpleLoaderLocalizer implements Contracts\Localizer
{
    /** @var array<string, bool> */
    private array $loadedLocales = [];

    /** @var array<string, array<string, string>> */
    private array $locales = [];

    private Closure $loader;

    public function __construct(?Closure $loader = null)
    {
        $this->loader = $loader ?? self::defaultLoader(...);
    }

    /**
     * @param string $locale
     * @return array<string, string>|null
     */
    private static function defaultLoader(string $locale): ?array
    {
        if (file_exists(__DIR__ . "/../Locales/{$locale}.php")) {
            return require __DIR__ . "/../Locales/{$locale}.php";
        }

        return null;
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, string>
     */
    public static function prepareParameters(array $parameters): array
    {
        return array_map(
            fn(mixed $value): string => is_scalar($value) ? (string)$value : Serialize::safeType($value),
            $parameters
        );
    }

    public function translate(string $locale, string $key, array $parameters = [], ?string $default = null): string
    {
        if ($this->hasExactTranslation($locale, $key)) {
            return $this->replaceParameters($this->locales[$locale][$key], $parameters);
        }

        if ($this->hasTranslation($locale, $key)) {
            [$language] = Locales::explodeIntoLanguageAndCountry($locale);
            return $this->replaceParameters($this->locales[$language][$key], $parameters);
        }

        return $default ?? $key;
    }

    public function hasTranslation(string $locale, string $key): bool
    {
        if ($this->hasExactTranslation($locale, $key)) {
            return true;
        }

        [$language, $country] = Locales::explodeIntoLanguageAndCountry($locale);
        return $country && $this->hasExactTranslation($language, $key);
    }

    public function hasExactTranslation(string $locale, string $key): bool
    {
        $this->loadLocaleOnce($locale);
        return isset($this->locales[$locale][$key]);
    }

    /**
     * @param string $target
     * @param array<string, mixed> $parameters
     * @return string
     */
    private function replaceParameters(string $target, array $parameters): string
    {
        $stringSafeParameters = self::prepareParameters($parameters);
        $keys = array_map(fn($key): string => ":{$key}", array_keys($stringSafeParameters));
        return str_replace($keys, array_values($stringSafeParameters), $target);
    }

    private function loadLocaleOnce(string $locale): void
    {
        $locale = Locales::normalizeLocaleString($locale);
        if (isset($this->loadedLocales[$locale])) {
            return;
        }

        $translations = ($this->loader)($locale);
        $this->loadedLocales[$locale] = true;
        $this->locales[$locale] = $translations;
    }
}