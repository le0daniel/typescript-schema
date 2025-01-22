<?php declare(strict_types=1);

namespace TypescriptSchema\Data;

final readonly class Options
{

    public function __construct(
        public bool $allowPartialFailures = false,
        public bool $validateWhenSerializing = true,
        public mixed $context = null,
    )
    {

    }

}