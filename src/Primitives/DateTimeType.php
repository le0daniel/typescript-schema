<?php declare(strict_types=1);

namespace TypescriptSchema\Primitives;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Exceptions\Issue;

final class DateTimeType extends PrimitiveType
{
    private static string $DEFAULT_FORMAT = DateTime::ATOM;

    /**
     * Use this to globally change the date time format to whatever fits your need.
     *
     * @param string $defaultFormat
     * @return void
     */
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
        $format ??= $this->format;
        return $this->addInternalTransformer(static function (DateTimeImmutable $timeImmutable) use ($format): string {
            return $timeImmutable->format($format ?? DateTimeType::$DEFAULT_FORMAT);
        }, 'string');
    }

    protected function toDefinition(): Definition
    {
        // Datetime has a different format for input and output, as by default when using json_serialize
        // it creates an object containing, date, timezone_type and timezone.
        return new Definition(
            'string',
            '{date: string, timezone_type: number, timezone: string}'
        );
    }
}