<?php declare(strict_types=1);

namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Tests\PluginTestCase;

class CurrencyTest extends PluginTestCase
{
    /**
     * Setup the test environment.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Check if Table-Seeder sets default currency correctly.
     * @return void
     */
    public function testHasOneDefaultCurrency()
    {
        $cur = Currency::where('is_default', 1)->count();
        $this->assertEquals($cur, 1);
        return $cur;
    }

    /**
     * Only one currency can be the default currency.
     * @return void
     */
    public function testOnlyOneDefaultCurrency()
    {
        $currency = Currency::where('is_default', 0)->first();
        $currency->is_default = true;
        $this->assertTrue($currency->save(), 'Currency could not be updated.');
        $this->assertEquals(Currency::where('is_default', 1)->count(), 1);
        return $currency;
    }

    /**
     * Disabled currencies cannot be the default currency.
     * @return void
     */
    public function testDisabledCurrenciesCannotBeDefault()
    {
        $currency = Currency::where('is_default', 0)->first();
        $currency->is_enabled = false;
        $this->assertTrue($currency->save(), 'Currency could not be updated.');

        $currency->is_default = true;
        $this->assertTrue($currency->save(), 'Currency could not be updated.');
        $this->assertFalse($currency->is_default, 'Currency is disabled and the default one at the same time.');

        return $currency;
    }

    /**
     * Disabled currencies cannot be the default currency.
     * @return void
     */
    public function testDisableDefaultCurrency()
    {
        $currency = Currency::where('is_default', 1)->first();
        $currency->is_default = false;
        $currency->is_enabled = false;
        $currency->save();
        $this->assertEquals(Currency::where('is_default', 1)->count(), 1, 'No default currency available.');

        Currency::where('is_default', 0)->update(['is_enabled' => 0]);
        $currency = Currency::where('is_default', 1)->first();
        $currency->is_default = false;
        $currency->is_enabled = false;
        $this->assertThrows(fn() => $currency->save(), \Throwable::class);
    }
}
