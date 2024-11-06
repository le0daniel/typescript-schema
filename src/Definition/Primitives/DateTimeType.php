<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use DateTimeImmutable;
use DateTimeInterface;
use Throwable;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\SerializesOutputValue;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Schema\Definition;
use TypescriptSchema\Definition\Shared\HasDefaultValue;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Definition\Shared\Validators;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

final class DateTimeType implements Type, SerializesOutputValue
{
    /** @use Nullable<DateTimeType> */
    use Validators, Nullable, Refinable, Transformable, HasDefaultValue;

    private static string $DEFAULT_FORMAT = DateTimeInterface::ATOM;

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
     * Accepts formatted string and returns the
     *
     * @param string|DateTimeInterface $value
     * @return DateTimeImmutable|Value
     */
    private function parseDateTime(string|DateTimeInterface $value): DateTimeImmutable|Value
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        try {
            $dateTime = DateTimeImmutable::createFromFormat($this->getFormat(), $value);
            if ($dateTime && $dateTime->format($this->getFormat()) === $value) {
                return $dateTime;
            }
        } catch (Throwable $e) {
            return Value::INVALID;
        }

        return Value::INVALID;
    }

    private function getFormat(): string
    {
        return $this->format ?? self::$DEFAULT_FORMAT;
    }

    public function before(DateTimeImmutable $before): DateTimeType
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

    public function after(DateTimeImmutable $after): DateTimeType
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

    public function toDefinition(): SchemaDefinition
    {
        return new Definition(
            [
                'type' => 'string',
                'description' => "Date Time string with Format: {$this->getFormat()}",
            ],
            [
                'type' => 'string',
                'description' => "Date Time string with Format: {$this->getFormat()}",
            ]
        );
    }

    public function parse(mixed $value, Context $context): DateTimeImmutable|Value
    {
        $value = $this->applyDefaultValue($value);

        if (!is_string($value) && !$value instanceof DateTimeInterface) {
            $context->addIssue(Issue::invalidType('DateTimeString', $value));
            return Value::INVALID;
        }

        $dateTime = $this->parseDateTime($value);
        if ($dateTime === Value::INVALID) {
            $context->addIssue(Issue::invalidType("DateTimeString<format: {$this->getFormat()}>", $value));
            return Value::INVALID;
        }

        if (!$this->runValidators($dateTime, $context)) {
            return Value::INVALID;
        }

        return $dateTime;
    }

    public function serializeValue(mixed $value, Context $context): string|Value
    {
        if (!$value instanceof DateTimeImmutable) {
            return Value::INVALID;
        }

        return $value->format($this->getFormat());
    }
}