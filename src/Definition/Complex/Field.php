<?php declare(strict_types=1);

namespace TypescriptSchema\Definition\Complex;

use Closure;
use DateTimeInterface;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Data\Enum\Value;
use TypescriptSchema\Definition\Shared\Describable;
use TypescriptSchema\Utils\Utils;

class Field
{
    use Describable;

    protected Closure $resolvedBy;
    protected bool $isOptional = false;

    /** @var array{0: string|null, 1: DateTimeInterface|null}|null  */
    protected array|null $deprecated = null;

    protected ?string $alias = null ;

    public function __construct(protected Type $type)
    {
    }

    public function optional(): self
    {
        $instance = clone $this;
        $instance->isOptional = true;
        return $instance;
    }

    /**
     * Set an alias for the field resolver. This is a shortcut
     * for ->resolvedBy(fn($data) => $data['alias'])
     * @param string $alias
     * @return $this
     */
    public function alias(string $alias): self
    {
        $clone = clone $this;
        $clone->alias = $alias;
        return $clone;
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

    private function defaultResolver(mixed $data, string $fieldName): mixed
    {
        $fieldName = $this->alias ?? $fieldName;
        // Handles the undefined case.
        if (!Utils::valueExists($fieldName, $data)) {
            return Value::UNDEFINED;
        }

        /** @var array<mixed>|object $data */

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
     * @internal
     */
    public function resolveValue(string $fieldName, mixed $data, mixed $userProvidedContext = null): mixed
    {
        return isset($this->resolvedBy)
            ? ($this->resolvedBy)($data, $fieldName, $userProvidedContext)
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

    public function deprecated(?string $reason = null, ?DateTimeInterface $removalDateTime = null): self
    {
        $instance = clone $this;
        $instance->deprecated = [$reason, $removalDateTime];
        return $instance;
    }

    /** @internal  */
    public function isDeprecated(): bool
    {
        return $this->deprecated !== null;
    }
}
