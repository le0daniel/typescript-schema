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
                [
                    'message' => 'Value needs to be of type string',
                    'path' => ['name']
                ],
                [
                    'message' => 'Value needs to be of type string',
                    'path' => ['name']
                ]
            ],
        ], $exception->jsonSerialize());

        self::assertEquals([
            'message' => 'Ungültige Daten',
            'issues' => [
                [
                    'message' => 'Wert muss vom Typ string sein',
                    'path' => ['name']
                ],
                [
                    'message' => 'Wert muss vom Typ string sein',
                    'path' => ['name']
                ]
            ],
        ], $exception->setLocale('de')->jsonSerialize());
    }

}
