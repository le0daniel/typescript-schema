<?php declare(strict_types=1);

namespace TypescriptSchema\Data\Enum;

enum SerializationMode
{
    case LIMITED;
    case ALL;
    case ALL_WITH_DEBUG;
}
