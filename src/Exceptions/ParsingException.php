<?php declare(strict_types=1);

namespace TypescriptSchema\Exceptions;

use Exception;
use JsonSerializable;
use TypescriptSchema\Data\HasLocalizer;
use TypescriptSchema\Utils\Issues;

class ParsingException extends Exception implements JsonSerializable
{
    use HasLocalizer;

    /**
     * @param array<Issue> $issues
     */
    public function __construct(
        public readonly array $issues,
    )
    {
        parent::__construct('Failed parsing the schema');
    }

    /**
     * @param bool $debug
     * @return array{message: string, issues: array<array{message: string, path: array<string|int>, exception?: mixed}>}
     */
    public function toArray(bool $debug = false): array
    {
        return [
            'message' => $this->getLocalizer()->translate($this->locale, 'failed'),
            'issues' => Issues::serialize($this->issues, $this->getLocalizer(), $this->locale, $debug)
        ];
    }

    /**
     * @return array{message: string, issues: array<array{message: string, path: array<string|int>, exception?: mixed}>}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
