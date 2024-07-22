<?php declare(strict_types=1);

namespace TypescriptSchema\Exceptions;

use Exception;
use JsonSerializable;
use TypescriptSchema\Contracts\Localizer;

class ParsingException extends Exception implements JsonSerializable
{
    private const string DEFAULT_LOCALE = 'en';

    private ?Localizer $localizer = null;

    private string $locale = self::DEFAULT_LOCALE;

    /**
     * @param array<Issue> $issues
     */
    public function __construct(
        public readonly array $issues
    )
    {
        parent::__construct('Failed parsing the schema');
    }

    private function getLocalizer(): Localizer
    {
        return $this->localizer ??= new \TypescriptSchema\Helpers\SimpleLoaderLocalizer();
    }

    public function setLocalizer(Localizer $localizer): self
    {
        $this->localizer = $localizer;
        return $this;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function toArray(): array
    {
        $groupedIssues = [];
        foreach ($this->issues as $issue) {
            $groupedIssues[$issue->pathAsString()][] = $this->getLocalizer()->translate(
                $this->locale, $issue->getLocalizationKey(), $issue->metadata,
            );
        }

        return [
            'message' => $this->getLocalizer()->translate($this->locale, 'failed'),
            'issues' => $groupedIssues,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
