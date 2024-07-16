<?php declare(strict_types=1);

namespace Tests\Definition\Complex;

use Iterator;
use Tests\Mocks\UnitEnumMock;
use TypescriptSchema\Definition\Complex\RecordType;
use PHPUnit\Framework\TestCase;
use TypescriptSchema\Definition\Primitives\LiteralType;
use TypescriptSchema\Definition\Primitives\StringType;

class RecordTypeTest extends TestCase
{

    public function testDefinition()
    {
        $same = RecordType::make(StringType::make());
        self::assertEquals('Record<string,string>', $same->toInputDefinition());
        self::assertEquals('Record<string,string>', $same->toOutputDefinition());

        $different = RecordType::make(LiteralType::make(UnitEnumMock::SUCCESS));
        self::assertEquals("Record<string,'SUCCESS'>", $different->toInputDefinition());
        self::assertEquals('Record<string,never>', $different->toOutputDefinition());
    }

    public function testExecutionWithArray()
    {
        $record = RecordType::make(StringType::make());
        self::assertEquals(['name' => 'value', 'email' => 'something'], $record->parse(['name' => 'value', 'email' => 'something']));
    }

    public function testExecutionWithObject()
    {
        $record = RecordType::make(StringType::make());

        $object = new class implements Iterator {

            private int $currentIndex = 0;

            private array $data = [
                ['name', 'value',],
                ['email', 'something'],
                ['wow', 'new'],
            ];

            public function current(): string
            {
                [$key, $value] = $this->data[$this->currentIndex];
                return $value;
            }

            public function next(): void
            {
                $this->currentIndex++;
            }

            public function key(): string
            {
                [$key, $value] = $this->data[$this->currentIndex];
                return $key;
            }

            public function valid(): bool
            {
                return $this->currentIndex < count($this->data);
            }

            public function rewind(): void
            {
                $this->currentIndex = 0;
            }
        };

        self::assertEquals(['name' => 'value', 'email' => 'something', 'wow' => 'new'], $record->parse($object));
    }

    public function testPathOfIssues()
    {
        $record = RecordType::make(StringType::make());
        $result = $record->safeParse(['name' => 'value', 'email' => null]);

        self::assertCount(1, $result->issues);
        self::assertEquals(['email'], $result->issues[0]->getPath());
    }

}
