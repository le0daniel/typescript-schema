<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Utils;

use PHPUnit\Framework\TestCase;
use TypescriptSchema\Tests\Mocks\ArrayAccessMock;
use TypescriptSchema\Tests\Mocks\GettersMock;
use TypescriptSchema\Utils\Utils;

class UtilsTest extends TestCase
{

    public function testValueExists()
    {
        self::assertTrue(Utils::valueExists('test', ['test' => 1]));
        self::assertTrue(Utils::valueExists('test', ['test' => null]));
        self::assertFalse(Utils::valueExists('test', ['other' => null]));

        $object = new \stdClass();
        $object->test = null;
        self::assertTrue(Utils::valueExists('test', $object));
        self::assertFalse(Utils::valueExists('other', $object));

        $classWithGetter = new class {
            public function __isset(string $name): bool
            {
                return $name === 'test';
            }

            public function __get(string $name)
            {
                return match ($name) {
                    'test' => 1,
                    default => null,
                };
            }
        };

        self::assertTrue(Utils::valueExists('test', $classWithGetter));
        self::assertFalse(Utils::valueExists('other', $classWithGetter));

        self::assertTrue(Utils::valueExists('test', new ArrayAccessMock(['test' => 1])));
        self::assertFalse(Utils::valueExists('other', new ArrayAccessMock(['test' => 1])));
    }

    public function testExtractValue()
    {
        self::assertSame(1, Utils::extractValue('test', ['test' => 1]));
        self::assertSame(null, Utils::extractValue('test', ['test' => null]));
        self::assertSame(null, Utils::extractValue('test', ['other' => null]));

        $object = new \stdClass();
        $object->test = 'one';

        self::assertSame('one', Utils::extractValue('test', $object));
        self::assertSame(null, Utils::extractValue('other', $object));

        self::assertSame('two', Utils::extractValue('test', new GettersMock(['test' => 'two'])));
        self::assertSame(null, Utils::extractValue('other', new GettersMock([])));

        self::assertSame('three', Utils::extractValue('test', new ArrayAccessMock(['test' => 'three'])));
        self::assertSame(null, Utils::extractValue('other', new ArrayAccessMock([])));
    }
}
