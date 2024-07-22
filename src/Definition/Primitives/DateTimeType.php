<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use TypescriptSchema\Data\Definition;
use TypescriptSchema\Definition\Shared\InternalTransformers;
use TypescriptSchema\Exceptions\Issue;

final class DateTimeType extends PrimitiveType
{
    use InternalTransformers;

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

    public function asFormattedString(?string $format = null): static
    {
        $format ??= $this->format;
        return $this->addInternalTransformer(static function (DateTimeImmutable $timeImmutable) use ($format): string {
            return $timeImmutable->format($format ?? DateTimeType::$DEFAULT_FORMAT);
        }, 'string');
    }

    public function before(DateTimeImmutable $before): static
    {
        return $this->addValidator(function (DateTimeImmutable $value) use ($before) {
            if ($value < $before) {
                return true;
            }

            throw Issue::custom("Date needs to be before {$before->format($this->getFormat())}.", [
                'before' => $before->format($this->getFormat()),
            ], localizationKey: 'datetime.invalid_before');
        });
    }

    public function after(DateTimeImmutable $after): static
    {
        return $this->addValidator(function (DateTimeImmutable $value) use ($after) {
            if ($value > $after) {
                return true;
            }

            throw Issue::custom("Date needs to be after {$after->format($this->getFormat())}.", [
                'after' => $after->format($this->getFormat()),
            ], localizationKey: 'datetime.invalid_after');
        });
    }

    public function toDefinition(): Definition
    {
        // Datetime has a different format for input and output, as by default when using json_serialize
        // it creates an object containing, date, timezone_type and timezone.
        return $this->applyTransformerToDefinition(
            new Definition(
                'string',
                '{date: string, timezone_type: number, timezone: string}'
            )
        );
    }
}