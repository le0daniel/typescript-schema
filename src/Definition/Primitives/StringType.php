<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use TypescriptSchema\Data\Definition;
use TypescriptSchema\Definition\Shared\InternalTransformers;
use TypescriptSchema\Exceptions\Issue;

/**
 * @extends PrimitiveType<string>
 */
class StringType extends PrimitiveType 
{
    use InternalTransformers;

    protected function parsePrimitiveType(mixed $value): string
    {
        if (!is_string($value) && !$value instanceof \Stringable) {
            throw Issue::invalidType($this->toDefinition()->input, $value);
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
        return $this->addInternalTransformer(strtoupper(...));
    }

    public function lowerCase(): static
    {
        return $this->addInternalTransformer(strtolower(...));
    }

    public function trim(): static
    {
        return $this->addInternalTransformer(static fn(string $value) => trim($value));
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

    public function nonEmpty(): static
    {
        return $this->addValidator(static function(string $value) {
            $trimmed = trim($value);
            if (empty($trimmed)) {
                throw Issue::custom("Value can not be empty.");
            }
            return true;
        });
    }

    public function alphaNumeric(): static
    {
        return $this->addValidator(static function (string $value) {
            if (preg_match('/^[A-Za-z0-9]*$/', $value) !== 1) {
                throw Issue::custom("Value is not a valid alphanumeric string.");
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

    public function startsWith(string $startsIn): static
    {
        return $this->addValidator(static function (string $value) use ($startsIn) {
            if (!str_starts_with($value, $startsIn)) {
                throw Issue::custom("Value must start with {$startsIn}.");
            }
            return true;
        });
    }

    public function toDefinition(): Definition
    {
        return Definition::same('string');
    }
}
