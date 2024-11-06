<?php declare(strict_types=1);

namespace TypescriptSchema\Data\Enum;

enum Status implements \JsonSerializable
{

    case SUCCESS;
    case FAILURE;
    case PARTIAL;

    public function jsonSerialize(): string
    {
        return $this->name;
    }
}
