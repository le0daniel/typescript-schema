<?php declare(strict_types=1);

namespace TypescriptSchema\Utils;

use TypescriptSchema\Contracts\ComplexType;
use TypescriptSchema\Contracts\NamedType;
use TypescriptSchema\Contracts\OptionallyNamed;
use TypescriptSchema\Contracts\Type;
use TypescriptSchema\Definition\Wrappers\WrapsType;

final class Types
{
    /**
     * @param Type $type
     * @return array<string|Type>
     */
    public static function extractNamesTypes(Type $type): array
    {
        $namedTypes = [];
        $stack = [$type];
        while ($type = array_pop($stack)) {
            $unwrapped = WrapsType::mostInnerType($type);

            if ($unwrapped instanceof ComplexType) {
                array_push($stack, ...array_values($unwrapped->getTypes()));
            }

            if (!$unwrapped instanceof NamedType && !$unwrapped instanceof OptionallyNamed) {
                continue;
            }

            $name = $unwrapped->getName();
            if (!$name) {
                continue;
            }

            $namedType = $unwrapped instanceof \TypescriptSchema\Definition\Complex\NamedType
                ? WrapsType::mostInnerType($unwrapped->type)
                : $unwrapped;

            $namedTypes[$name] = $namedType;
        }
        return $namedTypes;
    }
}