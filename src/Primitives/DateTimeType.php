<?php declare(strict_types=1);

namespace TypescriptSchema\Primitives;

use DateTimeImmutable;
use DateTimeInterface;
use TypescriptSchema\Data\TypescriptDefinition;
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

    public static function make(?string $format = null): static
    {
        return new self($format);
    }

    /**
     * @throws Issue
     */
    protected function parsePrimitiveType(mixed $value): DateTimeImmutable
    {
        if (is_string($value)) {
            return $this->parseDateTimeString($value);
        }

        if (!$value instanceof DateTimeInterface) {
            throw Issue::invalidType('DateTime', $value);
        }

        return $value instanceof DateTimeImmutable
            ? $value
            : DateTimeImmutable::createFromInterface($value);
    }

    protected function parseDateTimeString(string $value): DateTimeImmutable
    {
        $dateTime = DateTimeImmutable::createFromFormat($this->getFormat(), $value);
        if ($dateTime && $dateTime->format($this->getFormat()) === $value) {
            return $dateTime;
        }

        throw Issue::invalidType("DateTimeString<format: {$this->getFormat()}>", $value);
    }

    protected function coerceValue(mixed $value): mixed
    {
        return $value;
    }

    private function getFormat(): string
    {
        return $this->format ?? self::$DEFAULT_FORMAT;
    }

    public function toFormattedString(?string $format = null): static
    {
        return $this->addInternalTransformer(function (DateTimeImmutable $timeImmutable) use ($format): string {
            return $timeImmutable->format($format ?? self::$DEFAULT_FORMAT);
        }, 'string');
    }

    protected function toDefinition(): TypescriptDefinition
    {
        return new TypescriptDefinition(
            'string',
            '{date: string, timezone_type: number, timezone: string}'
        );
    }
}