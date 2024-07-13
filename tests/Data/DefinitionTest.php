<?php declare(strict_types=1);

namespace Tests\Data;

use TypescriptSchema\Data\Definition;
use PHPUnit\Framework\TestCase;

class DefinitionTest extends TestCase
{

    public function testToDefinition(): void
    {
        self::assertEquals('int', Definition::same('int')->toInputDefinition());
        self::assertEquals('string', (new Definition('string', 'int'))->toInputDefinition());
        self::assertEquals('int', (new Definition('string', 'int'))->toOutputDefinition());
    }

}
