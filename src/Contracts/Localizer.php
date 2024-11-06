<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

interface Localizer
{

    /**
     * If no locale is passed, the default is returned if given, otherwise the key.
     *
     * @param array<string, mixed> $parameters
     * @throws \RuntimeException
     */
    public function translate(string $locale, string $key, array $parameters = [], ?string $default = null): string;

    /**
     * Should return true if a translation is available for the language only.
     * Example:
     *
     * Available: de, de-DE
     * - de => true
     * - de-CH => true
     * - fr => false
     * - fr-FR => false
     *
     * @throws \RuntimeException
     */
    public function hasTranslation(string $locale, string $key): bool;

    /**
     * Should determine if there is a translation available for the exact locale.
     * Example: de-CH, fr-FR
     *
     * @throws \RuntimeException
     */
    public function hasExactTranslation(string $locale, string $key): bool;
}