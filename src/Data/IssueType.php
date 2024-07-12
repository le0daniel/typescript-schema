<?php declare(strict_types=1);

namespace TypescriptSchema\Data;

use JsonSerializable;

enum IssueType implements JsonSerializable
{
    case INVALID_TYPE;
    case COERCION_FAILURE;

    case INVALID_KEY;
    case CUSTOM;

    case INTERNAL_ERROR;

    public function jsonSerialize(): string
    {
        return $this->name;
    }
}
