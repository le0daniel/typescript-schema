<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

interface Localizer
{

    /**
     * If no locale is passed, the default is returned if given, otherwise the key.
     *
     * @throws \RuntimeException
     */
    public function translate(string $locale, string $key, array $parameters = [], ?string $default = null): string;

    /** @throws \RuntimeException */
    public function hasTranslation(string $locale, string $key): bool;

    /** @throws \RuntimeException */
    public function hasExactTranslation(string $locale, string $key): bool;
}