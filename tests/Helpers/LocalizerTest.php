<?php declare(strict_types=1);

namespace TypescriptSchema\Tests\Helpers;

use TypescriptSchema\Helpers\Localizer;
use PHPUnit\Framework\TestCase;

class LocalizerTest extends TestCase
{

    public function testLocalize()
    {
        $localizer = new Localizer(fn(string $locale) => match ($locale) {
            'de' => [
                'some_value' => 'Irgendein Wert: :value',
            ],
            'en' => [
                'some_value' => 'Some value: :value',
            ],
            default => null,
        });

        self::assertEquals('Irgendein Wert: test', $localizer->translate('de', 'some_value', ['value' => 'test']));
        self::assertEquals('Irgendein Wert: test', $localizer->translate('de-CH', 'some_value', ['value' => 'test']));
        self::assertEquals('Irgendein Wert: test', $localizer->translate('de-ch', 'some_value', ['value' => 'test']));
        self::assertEquals('Irgendein Wert: test', $localizer->translate('de_ch', 'some_value', ['value' => 'test']));
        self::assertEquals('Irgendein Wert: test', $localizer->translate('de_CH', 'some_value', ['value' => 'test']));

        self::assertEquals('Some value: test', $localizer->translate('en-GB', 'some_value', ['value' => 'test']));

        self::assertEquals('some_value', $localizer->translate('fr', 'some_value', ['value' => 'test']));
        self::assertEquals('Default', $localizer->translate('fr', 'some_value', ['value' => 'test'], 'Default'));
    }

    public function testLocalesLoadingOnce()
    {
        $count = 0;
        $localizer = new Localizer(function () use (&$count) {
            $count++;
            return [
                'some' => 'translation',
            ];
        });

        self::assertEquals('translation', $localizer->translate('fr', 'some'));
        self::assertEquals('other', $localizer->translate('fr', 'other'));
        self::assertEquals(1, $count);
    }

    public function testLocaleAndCountryPriority()
    {
        $localizer = new Localizer(fn(string $locale) => match ($locale) {
            'de_CH' => [
                'some_value' => 'Irgend en Wert: :value',
            ],
            'de' => [
                'some_value' => 'Irgendein Wert: :value',
                'other' => 'Anderer Wert: :value',
            ],
            default => null,
        });

        self::assertEquals('Irgend en Wert: test', $localizer->translate('de_CH', 'some_value', ['value' => 'test']));
        self::assertEquals('Irgendein Wert: test', $localizer->translate('de', 'some_value', ['value' => 'test']));
        self::assertEquals('Anderer Wert: test', $localizer->translate('de_CH', 'other', ['value' => 'test']));
    }

    public function testDefaultLocalizer()
    {
        $localizer = new Localizer();
        self::assertEquals('Invalid data', $localizer->translate('en', 'failed'));
        self::assertEquals('Ungültige Daten', $localizer->translate('de', 'failed'));
    }

    public function testPrepareParameters()
    {
        self::assertSame([], Localizer::prepareParameters([]));
        self::assertSame(['key' => '1.22'], Localizer::prepareParameters(['key' => 1.22]));
        self::assertSame(['key' => 'string'], Localizer::prepareParameters(['key' => 'string']));
        self::assertSame(['key' => 'object'], Localizer::prepareParameters(['key' => new \stdClass()]));
        self::assertSame(['key' => '1'], Localizer::prepareParameters(['key' => true]));
        self::assertSame(['key' => '1'], Localizer::prepareParameters(['key' => 1]));
        self::assertSame(['key' => 'closure'], Localizer::prepareParameters(['key' => fn() => null]));
    }
}
