<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Utils;

use TypescriptSchema\Utils\Locales;
use PHPUnit\Framework\TestCase;

class LocalesTest extends TestCase
{

    public function testExplodeIntoLanguageAndCountry()
    {
        self::assertEquals(['de', null], Locales::explodeIntoLanguageAndCountry('de'));
        self::assertEquals(['de', 'CH'], Locales::explodeIntoLanguageAndCountry('de_ch'));
        self::assertEquals(['de', 'CH'], Locales::explodeIntoLanguageAndCountry('de_CH'));
        self::assertEquals(['de', 'CH'], Locales::explodeIntoLanguageAndCountry('de-CH'));
        self::assertEquals(['de', 'CH'], Locales::explodeIntoLanguageAndCountry('de-ch'));
    }
}
