<?php declare(strict_types=1);

namespace TypescriptSchema\Data;

use TypescriptSchema\Contracts\Localizer;
use TypescriptSchema\Helpers\SimpleLoaderLocalizer;

trait HasLocalizer
{
    private const string DEFAULT_LOCALE = 'en';
    private ?Localizer $localizer = null;
    private string $locale = self::DEFAULT_LOCALE;

    public function setLocalizer(?Localizer $localizer): static
    {
        $this->localizer = $localizer;
        return $this;
    }

    private function getLocalizer(): Localizer
    {
        return $this->localizer ??= new SimpleLoaderLocalizer();
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

}