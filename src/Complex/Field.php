<?php declare(strict_types=1);

namespace TypescriptSchema\Complex;

use Closure;
use TypescriptSchema\Data\Value;
use TypescriptSchema\Schema;
use TypescriptSchema\Type;
use TypescriptSchema\Utils\Utils;

class Field
{
    protected Closure $resolvedBy;
    protected bool $isOptional = false;

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
     * Define a custom resolve function that is used to resolve the value to this field.
     * This is useful when dealing with Objects or deprecated fields.
     *
     * Example:
     *
     *     ObjectType::make([
     *         'oldField' => Field::ofType(StringType::make())
     *             ->resolvedBy(function(MyObject $object): string {
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

    private function defaultResolver(string $key, mixed $data): mixed
    {
        // Handles the undefined case.
        if (!Utils::valueExists($key, $data)) {
            return Value::UNDEFINED;
        }

        return Utils::extractValue($key, $data);
    }

    public static function ofType(Type $type): self
    {
        return new self($type);
    }

    /**
     * @internal
     * @param string $key
     * @param mixed $data
     * @return mixed
     */
    public function resolveToValue(string $key, mixed $data): mixed
    {
        return isset($this->resolvedBy)
            ? ($this->resolvedBy)($key, $data)
            : $this->defaultResolver($key, $data);
    }

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

}
