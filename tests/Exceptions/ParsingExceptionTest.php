<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Exceptions;

use TypescriptSchema\Exceptions\Issue;
use TypescriptSchema\Exceptions\ParsingException;
use TypescriptSchema\Tests\TestCase;

class ParsingExceptionTest extends TestCase
{

    public function testSerialization(): void
    {
        $exception = new ParsingException([
            Issue::invalidType('string', 'array', ['name']),
            Issue::coercionFailure('string', 'array', ['name']),
        ]);

        self::assertEquals([
            'message' => 'Invalid data',
            'issues' => [
                'name' => [
                    'Value needs to be of type string',
                    'Value needs to be of type string',
                ]
            ],
        ], $exception->jsonSerialize());

        self::assertEquals([
            'message' => 'Ungültige Daten',
            'issues' => [
                'name' => [
                    'Wert muss vom Typ string sein',
                    'Wert muss vom Typ string sein',
                ]
            ],
        ], $exception->setLocale('de')->jsonSerialize());
    }

}
