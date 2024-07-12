<?php declare(strict_types=1);

namespace TypescriptSchema\Exceptions;

use Exception;

class ParsingException extends Exception
{

    public function __construct(
        public readonly array $issues
    )
    {
        parent::__construct('Failed parsing the schema');
    }
}
