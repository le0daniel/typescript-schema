<?php declare(strict_types=1);

namespace TypescriptSchema\Utils;

use BackedEnum;
use RuntimeException;
use UnitEnum;

final class Typescript
{
    public static function fromJsonSchema(array $definition): string
    {
        if (empty($definition)) {
            return 'any';
        }

        if (isset($definition['const'])) {
            return self::literal($definition['const']);
        }

        if (isset($definition['enum'])) {
            return implode('|', array_map(self::literal(...), $definition['enum']));
        }

        if (isset($definition['anyOf'])) {
            return implode('|', array_map(self::fromJsonSchema(...), $definition['anyOf']));
        }

        if (isset($definition['oneOf'])) {
            return implode('|', array_map(self::fromJsonSchema(...), $definition['oneOf']));
        }

        if (isset($definition['type'])) {
            return match ($definition['type']) {
                'integer', 'number' => 'number',
                'string' => 'string',
                'boolean' => 'boolean',
                'null' => 'null',

                // ToDo: proper typescript support needed
                'object' => self::objectDefinition(self::withoutKey($definition, 'type')),
                'array' => self::arrayDefinition($definition),
                default => throw new RuntimeException("Unsupported configuration: " . json_encode($definition, JSON_THROW_ON_ERROR))
            };
        }

        throw new RuntimeException("Unsupported configuration: " . json_encode($definition, JSON_THROW_ON_ERROR));
    }

    public static function literal(string|int|float|bool|null $value): string
    {
        return match (gettype($value)) {
            'integer', 'double' => (string)$value,
            'boolean' => self::boolToStringLiteral($value),
            'string' => self::wrapInSingleQuote($value),
            default => 'null',
        };
    }

    public static function doc(array $lines): string
    {
        $linesAsStrings = implode(PHP_EOL . ' * ', $lines);
        return <<<DOCBLOCK
/**
 * {$linesAsStrings}
 */
DOCBLOCK;

    }

    private static function arrayDefinition(array $definition): string
    {
        if (isset($definition['items']) && is_array($definition['items'])) {
            return 'Array<' . self::fromJsonSchema($definition['items']) . '>';
        }

        if (isset($definition['prefixItems'])) {
            $types = array_map(self::fromJsonSchema(...), $definition['prefixItems']);
            return '[' . implode(',', $types) . ']';
        }

        return 'Array<any>';
    }

    private static function objectDefinition(array $definition): string
    {
        // We accept everything
        if (empty($definition)) {
            return "{[key: string]:unknown}";
        }

        $requiredProperties = $definition['required'] ?? [];

        $properties = [];
        foreach ($definition['properties'] ?? [] as $name => $typeDefinition) {
            $isOptional = in_array($name, $requiredProperties, true) ? '' : '?';
            $escapedName = self::escapeObjectKey($name);
            $docBlock = self::docblockFromConfig($typeDefinition);
            $properties[] = "{$docBlock}{$escapedName}{$isOptional}:" .  self::fromJsonSchema($typeDefinition);
        }

        $properties[] = match ($definition['additionalProperties'] ?? true) {
            true => '[key: string]:unknown',
            false => null,
            default => '[key: string]:' . self::fromJsonSchema($definition['additionalProperties']),
        };

        $parameters = implode(';', array_filter($properties));
        return "{{$parameters}}";
    }

    private static function escapeObjectKey(string $name): string
    {
        return preg_match('/^[a-zA-Z][a-zA-Z\d_]*$/', $name) === 1
            ? $name
            : self::wrapInSingleQuote($name);
    }

    private static function withoutKey(array $array, string $key): array
    {
        if (isset($array[$key])) {
            unset($array[$key]);
        }
        return $array;
    }

    private static function docblockFromConfig(array $config): string
    {
        $lines = array_filter([
            $config['title'] ?? null,
            $config['description'] ?? null,
            isset($config['deprecated']) && $config['deprecated'] ? '@deprecated' : null,
        ]);

        return empty($lines) ? '' : self::doc($lines);
    }

    private static function boolToStringLiteral(bool $value): string
    {
        return $value ? 'true' : 'false';
    }

    private static function wrapInSingleQuote(string $input): string
    {
        $stringSafeInput = addslashes($input);
        return "'{$stringSafeInput}'";
    }
}