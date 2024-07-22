<?php declare(strict_types=1);

namespace TypescriptSchema\Contracts;

interface Localizer
{

    public function localize(string $locale, string $key, array $parameters = [], ?string $default = null): string;

}