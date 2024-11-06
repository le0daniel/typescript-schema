<?php declare(strict_types=1);

namespace TypescriptSchema\Utils;

use TypescriptSchema\Contracts\Localizer;
use TypescriptSchema\Exceptions\Issue;

final class Issues
{

    /**
     * @param array<Issue> $issues
     * @param Localizer $localizer
     * @param string $locale
     * @param bool $debug
     * @return array<array{message: string, path: array<string|int>, exception?: mixed}>
     */
    public static function serialize(array $issues, Localizer $localizer, string $locale, bool $debug = false): array
    {
        return array_map(function (Issue $issue) use ($debug, $localizer, $locale): array {
            $baseMessage = [
                'message' => $localizer->translate($locale, $issue->getLocalizationKey(), $issue->metadata),
                'path' => $issue->getPath(),
            ];

            if (!$debug || !$issue->getPrevious()) {
                return $baseMessage;
            }

            $exception = $issue->getPrevious();
            $baseMessage['exception'] = [
                'message' => $exception->getMessage(),
                'metadata' => $issue->metadata,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ];

            return $baseMessage;
        }, $issues);
    }

    /**
     * @param array<Issue> $issues
     * @param Localizer $localizer
     * @param string $locale
     * @return array<string, array<string>>
     */
    public static function serializeGrouped(array $issues, Localizer $localizer, string $locale): array
    {
        $grouped = [];
        foreach ($issues as $issue) {
            $grouped[$issue->pathAsString()][] = $localizer->translate($locale, $issue->getLocalizationKey(), $issue->metadata);
        }
        return $grouped;
    }

}