<?php declare(strict_types=1);

namespace Tests\Utils;

use TypescriptSchema\Data\Value;
use TypescriptSchema\Schema;
use TypescriptSchema\Utils\Utils;
use PHPUnit\Framework\TestCase;

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

        $classWithArrayAccess = new class implements \ArrayAccess
        {

            public function offsetExists(mixed $offset): bool
            {
                return $offset === 'test';
            }

            public function offsetGet(mixed $offset): mixed
            {
                // TODO: Implement offsetGet() method.
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
                // TODO: Implement offsetSet() method.
            }

            public function offsetUnset(mixed $offset): void
            {
                // TODO: Implement offsetUnset() method.
            }
        };

        self::assertTrue(Utils::valueExists('test', $classWithArrayAccess));
        self::assertFalse(Utils::valueExists('other', $classWithArrayAccess));
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

        $classWithGetter = new class {
            public function __isset(string $name): bool
            {
                return $name === 'test';
            }

            public function __get(string $name)
            {
                return match ($name) {
                    'test' => 'two',
                    default => null,
                };
            }
        };

        self::assertSame('two', Utils::extractValue('test', $classWithGetter));
        self::assertSame(null, Utils::extractValue('other', $classWithGetter));

        $classWithArrayAccess = new class implements \ArrayAccess
        {

            public function offsetExists(mixed $offset): bool
            {
                return $offset === 'test';
            }

            public function offsetGet(mixed $offset): mixed
            {
                return $offset === 'test' ? 'three' : null;
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
                // TODO: Implement offsetSet() method.
            }

            public function offsetUnset(mixed $offset): void
            {
                // TODO: Implement offsetUnset() method.
            }
        };

        self::assertSame('three', Utils::extractValue('test', $classWithArrayAccess));
        self::assertSame(null, Utils::extractValue('other', $classWithArrayAccess));
    }
}
