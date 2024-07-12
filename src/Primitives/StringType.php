<?php declare(strict_types=1);

namespace TypescriptSchema\Primitives;

use DateTimeImmutable;
use TypescriptSchema\Exceptions\Issue;

/**
 * @extends PrimitiveType<string>
 */
class StringType extends PrimitiveType 
{
    protected function parsePrimitiveType(mixed $value): string
    {
        if (!is_string($value)) {
            throw Issue::invalidType($this->toDefinition(), $value);
        }

        return (string) $value;
    }

    protected function coerceValue(mixed $value): string
    {
        try {
            return (string) $value;
        } catch (\Throwable) {
            throw Issue::coercionFailure('string', $value);
        }
    }

    /**
     * Transforms the result to uppercase after successful validation.
     * @return self
     */
    public function upperCase(): static
    {
        return $this->addTransformer(strtoupper(...));
    }

    public function lowerCase(): static
    {
        return $this->addTransformer(strtolower(...));
    }

    public function trim(): static
    {
        return $this->addTransformer(static fn(string $value) => trim($value));
    }

    public function regex(string $regex): static
    {
        return $this->addValidator(static function(string $value) use ($regex) {
            if (preg_match($regex, $value) !== 1) {
                throw Issue::custom("Value did not match expected pattern.");
            }
            return true;
        });
    }

    public function email(): static
    {
        return $this->addValidator(static function(string $value) {
            if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                throw Issue::custom("Value is not a valid email address.");
            }
            return true;
        });
    }

    public function min(int $min): static
    {
        return $this->addValidator(static function (string $value) use ($min) {
            if (strlen($value) < $min) {
                throw Issue::custom("Minimum value must be at least {$min}.");
            }
            return true;
        });
    }

    public function max(int $max): static
    {
        return $this->addValidator(static function (string $value) use ($max) {
            if (strlen($value) > $max) {
                throw Issue::custom("Maximum value must be at most {$max}.");
            }
            return true;
        });
    }

    public function endsWith(string $endsIn): static
    {
        return $this->addValidator(static function (string $value) use ($endsIn) {
            if (!str_ends_with($value, $endsIn)) {
                throw Issue::custom("Value must end with {$endsIn}.");
            }
            return true;
        });
    }

    public function dateTime(string $format = 'Y-m-d H:i:s'): static
    {
        return $this->addValidator(static function(string $value) use ($format) {
            $dateTime = DateTimeImmutable::createFromFormat($format, $value);
            if (!$dateTime || $dateTime->format($format) !== $value) {
                throw Issue::custom("Value is not a valid date time format ({$format}).");
            }
            return true;
        });
    }

    public function toDefinition(): string
    {
        return 'string';
    }
}
