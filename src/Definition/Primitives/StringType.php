<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Primitives;

use Stringable;
use Throwable;
use TypescriptSchema\Contracts\SchemaDefinition;
use TypescriptSchema\Contracts\SerializesOutputValue;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Data\Schema\Definition;
use TypescriptSchema\Definition\Shared\Coerce;
use TypescriptSchema\Definition\Shared\HasDefaultValue;
use TypescriptSchema\Definition\Shared\Nullable;
use TypescriptSchema\Definition\Shared\Refinable;
use TypescriptSchema\Definition\Shared\Transformable;
use TypescriptSchema\Definition\Shared\Validators;
use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Helpers\Context;

class StringType implements Type, SerializesOutputValue
{
    /** @use Nullable<StringType> */
    use Nullable, Coerce, Validators, Refinable, Transformable, HasDefaultValue;

    public static function make(): StringType
    {
        return new self();
    }

    protected function coerceValue(mixed $value): string|Value
    {
        return (string)$value;
    }

    public function regex(string $regex): static
    {
        return $this->addValidator(static function (string $value) use ($regex) {
            if (preg_match($regex, $value) !== 1) {
                throw Issue::custom(
                    "Value did not match expected pattern.",
                    ['regex' => $regex],
                    localizationKey: 'string.invalid_regex'
                );
            }
            return true;
        });
    }

    public function email(): static
    {
        return $this->addValidator(static function (string $value) {
            if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                throw Issue::custom("Value is not a valid email address.", localizationKey: 'string.invalid_email');
            }
            return true;
        });
    }

    public function minLength(int $min): static
    {
        return $this->addValidator(static function (string $value) use ($min) {
            if (strlen($value) < $min) {
                throw Issue::custom(
                    "Minimum value must be at least {$min}.",
                    ['min' => $min],
                    localizationKey: 'string.invalid_min'
                );
            }
            return true;
        });
    }

    public function nonEmpty(): static
    {
        return $this->addValidator(static function (string $value) {
            $trimmed = trim($value);
            if (empty($trimmed)) {
                throw Issue::custom(
                    "Value can not be empty.",
                    localizationKey: 'string.invalid_empty'
                );
            }
            return true;
        });
    }

    public function alphaNumeric(): static
    {
        return $this->addValidator(static function (string $value) {
            if (preg_match('/^[A-Za-z0-9]*$/', $value) !== 1) {
                throw Issue::custom(
                    "Value is not a valid alphanumeric string.",
                    localizationKey: 'string.invalid_alphanumeric'
                );
            }
            return true;
        });
    }

    public function maxLength(int $max): static
    {
        return $this->addValidator(static function (string $value) use ($max) {
            if (strlen($value) > $max) {
                throw Issue::custom(
                    "Maximum value must be at most {$max}.",
                    ['max' => $max],
                    localizationKey: 'string.invalid_max'
                );
            }
            return true;
        });
    }

    public function endsWith(string $endsIn): static
    {
        return $this->addValidator(static function (string $value) use ($endsIn) {
            if (!str_ends_with($value, $endsIn)) {
                throw Issue::custom(
                    "Value must end with {$endsIn}.",
                    ['endsWith' => $endsIn],
                    localizationKey: 'string.invalid_ends_with'
                );
            }
            return true;
        });
    }

    public function startsWith(string $startsIn): static
    {
        return $this->addValidator(static function (string $value) use ($startsIn) {
            if (!str_starts_with($value, $startsIn)) {
                throw Issue::custom(
                    "Value must start with {$startsIn}.",
                    ['startsWith' => $startsIn],
                    localizationKey: 'string.invalid_starts_with'
                );
            }
            return true;
        });
    }

    public function toDefinition(): SchemaDefinition
    {
        return Definition::same([
            'type' => 'string'
        ]);
    }

    public function parse(mixed $value, Context $context): Value|string
    {
        $value = $this->applyCoercionIfEnabled(
            $this->applyDefaultValue($value)
        );

        if (!is_string($value)) {
            $context->addIssue(Issue::invalidType('string', $value));
            return Value::INVALID;
        }

        if (!$this->runValidators($value, $context)) {
            return Value::INVALID;
        }

        return $value;
    }

    public function serializeValue(mixed $value, Context $context): mixed
    {
        return $this->parse(
            $value instanceof Stringable ? (string) $value : $value,
            $context
        );
    }
}
