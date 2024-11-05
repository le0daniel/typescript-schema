<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Utils;

use TypescriptSchema\Utils\Locales;
use TypescriptSchema\Tests\TestCase;

class LocalesTest extends TestCase
{

    public function testExplodeIntoLanguageAndCountry()
    {
        self::assertEquals(['de', null], Locales::explodeIntoLanguageAndCountry('de'));
        self::assertEquals(['de', 'CH'], Locales::explodeIntoLanguageAndCountry('de_ch'));
        self::assertEquals(['de', 'CH'], Locales::explodeIntoLanguageAndCountry('de_CH'));
        self::assertEquals(['de', 'CH'], Locales::explodeIntoLanguageAndCountry('de-CH'));
        self::assertEquals(['de', 'CH'], Locales::explodeIntoLanguageAndCountry('de-ch'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid locale given: de@CH');
        Locales::explodeIntoLanguageAndCountry('de@CH');
    }

    public function testIsValidLocaleString(): void
    {
        self::assertTrue(Locales::isValidLocaleString('de'));
        self::assertTrue(Locales::isValidLocaleString('de_ch'));
        self::assertTrue(Locales::isValidLocaleString('de_CH'));
        self::assertTrue(Locales::isValidLocaleString('de-CH'));
        self::assertTrue(Locales::isValidLocaleString('de-ch'));

        self::assertFalse(Locales::isValidLocaleString('de.'));
        self::assertFalse(Locales::isValidLocaleString(''));
        self::assertFalse(Locales::isValidLocaleString('CH_de'));
    }

    public function testNormalizeLocaleString(): void
    {
        self::assertEquals('de', Locales::normalizeLocaleString('de'));
        self::assertEquals('de_CH', Locales::normalizeLocaleString('de_ch'));
        self::assertEquals('de_CH', Locales::normalizeLocaleString('de_CH'));
        self::assertEquals('de_CH', Locales::normalizeLocaleString('de-CH'));
        self::assertEquals('de_CH', Locales::normalizeLocaleString('de-ch'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid locale given: de@CH');
        Locales::normalizeLocaleString('de@CH');
    }
}
