<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use Closure;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Shared\Describable;
use TypescriptSchema\Utils\Typescript;
use TypescriptSchema\Utils\Utils;

class Field
{
    use Describable;

    protected Closure $resolvedBy;
    protected bool $isOptional = false;
    protected array|null $deprecated = null;
    protected bool $isOnlyOutput = false;

    public function __construct(protected Type $type)
    {
    }

    public function onlyOutput(): self
    {
        $instance = clone $this;
        $instance->isOnlyOutput = true;
        return $instance;
    }

    public function optional(): self
    {
        $instance = clone $this;
        $instance->isOptional = true;
        return $instance;
    }

    /**
     * Define a custom resolve function that is used to resolve the value to this field.
     * This is useful when dealing with Objects or deprecated fields.
     *
     * Example:
     *
     *     ObjectType::make([
     *         'oldField' => Field::ofType(StringType::make())
     *             ->resolvedBy(function(MyObject $object, string $fieldName): string {
     *                 return $object->getFullName();
     *             })
     *     ])
     *
     * @param Closure(mixed):mixed $resolvedBy
     * @return $this
     */
    public function resolvedBy(Closure $resolvedBy): self
    {
        $instance = clone $this;
        $instance->resolvedBy = $resolvedBy;
        return $instance;
    }

    /**
     * @internal
     * @return bool
     */
    public function isIsOnlyOutput(): bool
    {
        return $this->isOnlyOutput;
    }

    private function defaultResolver(mixed $data, string $fieldName): mixed
    {
        // Handles the undefined case.
        if (!Utils::valueExists($fieldName, $data)) {
            return Value::UNDEFINED;
        }

        return Utils::extractValue($fieldName, $data);
    }

    public static function ofType(Type $type): self
    {
        return new self($type);
    }

    /**
     * @param string $fieldName
     * @param mixed $data
     * @return mixed
     *@internal
     */
    public function resolveValue(string $fieldName, mixed $data): mixed
    {
        return isset($this->resolvedBy)
            ? ($this->resolvedBy)($data, $fieldName)
            : $this->defaultResolver($data, $fieldName);
    }

    /**
     * @internal
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @internal
     */
    public function isOptional(): bool
    {
        return $this->isOptional;
    }

    public function deprecated(?string $reason = null, ?\DateTimeInterface $removalDateTime = null): self
    {
        $instance = clone $this;
        $instance->deprecated = [$reason, $removalDateTime];
        return $instance;
    }

    /**
     * @internal
     */
    public function hasDocBlock(): bool
    {
        return !empty($this->description);
    }

    private function descriptionAsLines(): array
    {
        return empty($this->description)
            ? []
            : explode(PHP_EOL, $this->description);
    }

    private function deprecatedLine(): ?string
    {
        if (!isset($this->deprecated)) {
            return null;
        }

        /** @var \DateTimeInterface|null $removalDateTime */
        /** @var ?string $reason */
        [$reason, $removalDateTime] = $this->deprecated;
        return match (true) {
            isset($reason) && isset($removalDateTime) => "@deprecated {$reason}. Removal Date: {$removalDateTime->format('Y-m-d')}",
            isset($reason) => "@deprecated {$reason}",
            isset($removalDateTime) => "@deprecated Removal Date: {$removalDateTime->format('Y-m-d')}",
            default => "@deprecated",
        };
    }

    /**
     * @internal
     */
    public function getDocBlock(): ?string
    {
        $hasDescription = !empty($this->description);
        $isDeprecated = $this->deprecated !== null;

        if (!$hasDescription && !$isDeprecated) {
            return null;
        }

        $lines = match (true) {
            $hasDescription && $isDeprecated => [
                ... $this->descriptionAsLines(),
                '',
                $this->deprecatedLine(),
            ],
            $hasDescription => $this->descriptionAsLines(),
            $isDeprecated => [$this->deprecatedLine()],
        };

        return Typescript::doc($lines);
    }
}
