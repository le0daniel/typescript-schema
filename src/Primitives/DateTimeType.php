<?php declare(strict_types=1);

namespace TypescriptSchema\Primitives;

use DateTimeImmutable;
use DateTimeInterface;
use TypescriptSchema\Exceptions\Issue;

final class DateTimeType extends PrimitiveType
{
    public const string DATABASE_FORMAT = 'Y-m-d H:i:s';
    private static string $DEFAULT_FORMAT = self::DATABASE_FORMAT;

    public static function setDefaultFormat(string $defaultFormat): void
    {
        self::$DEFAULT_FORMAT = $defaultFormat;
    }

    public function __construct(private readonly ?string $format)
    {
    }

    private function getFormat(): string
    {
        return $this->format ?? self::$DEFAULT_FORMAT;
    }

    public static function make(?string $format = null): static
    {
        return new self($format);
    }

    protected function parsePrimitiveType(mixed $value): DateTimeImmutable
    {
        if (!$value instanceof DateTimeInterface) {
            throw Issue::invalidType('DateTime', $value);
        }

        return $value instanceof DateTimeImmutable
            ? $value
            : DateTimeImmutable::createFromInterface($value);
    }

    protected function coerceValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $dateTime = DateTimeImmutable::createFromFormat($this->getFormat(), $value);
        if ($dateTime && $dateTime->format($this->getFormat()) === $value) {
            return $dateTime;
        }

        return $value;
    }

    public function toDefinition(): string
    {
        return '{date: string, timezone_type: number, timezone: string}';
    }
}