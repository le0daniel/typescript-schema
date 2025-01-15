<?php declare(strict_types=1);

namespace TypescriptSchema\Utils;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use RuntimeException;

final class ObjectCaster
{
    /**
     * @throws ReflectionException
     */
    public static function cast(string $className, mixed $data)
    {
        $reflection = new ReflectionClass($className);
        if ($reflection->hasMethod('from') && $reflection->getMethod('from')->isStatic()) {
            return $className::from($data);
        }

        // Empty array lists are acceptable for casting.
        // In this case, the value is just passed to the constructor
        if (!is_array($data) || (array_is_list($data) && !empty($data))) {
            return new $className($data);
        }

        // In case a constructor is set, we apply the values to the constructor.
        $constructor = $reflection->getConstructor();
        if ($constructor && !empty($constructor->getParameters())) {
            return self::castConstructor($reflection, $data);
        }

        $instance = $reflection->newInstance();
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            if (!$property->isPublic()) {
                continue;
            }

            $instance->{$property->getName()} = self::castValueFor(
                $property,
                $data[$property->getName()] ?? null,
                array_key_exists($property->getName(), $data),
            );
        }
        return $instance;
    }

    private static function castValueFor(ReflectionProperty|ReflectionParameter $reflection, mixed $value, bool $hasValue): mixed
    {
        $type = $reflection->getType();
        $isNamedType = $type instanceof ReflectionNamedType;

        // No further casting required
        if (!$type || !$isNamedType || $type->isBuiltin()) {
            return $value;
        }

        if (!is_null($value)) {
            return self::cast($type->getName(), $value);
        }

        // In case a default value is available, this one is used.
        // In case NULL is explicitly passed, NULL is always used as value.
        if (!$hasValue && $reflection->isDefaultValueAvailable()) {
            return $reflection->getDefaultValue();
        }

        /** @var ReflectionNamedType $type */
        if ($type->allowsNull()) {
            return null;
        }

        if ($reflection instanceof ReflectionParameter) {
            throw new RuntimeException("Can not set value NULL for {$reflection->getDeclaringClass()}__construct({$reflection->getName()})");
        }

        throw new RuntimeException("Can not set value NULL for {$reflection->getDeclaringClass()}->{$reflection->getName()}");
    }

    private static function castConstructor(ReflectionClass $reflection, array $data): mixed
    {
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        if (empty($parameters)) {
            return $reflection->newInstance();
        }

        $parameterValues = [];
        foreach ($parameters as $parameter) {
            $parameterValues[$parameter->getName()] = self::castValueFor(
                $parameter,
                $data[$parameter->getName()] ?? null,
                array_key_exists($parameter->getName(), $data)
            );
        }

        return $reflection->newInstanceArgs($parameterValues);
    }
}