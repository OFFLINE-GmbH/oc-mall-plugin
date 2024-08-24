<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Tests\Models;

use Exception;
use OFFLINE\Mall\Classes\User\Auth;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Tests\PluginTestCase;
use RainLab\User\Models\User;
use Throwable;

class CurrencyTest extends PluginTestCase
{
    /**
     * Setup the test environment.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        Auth::login(User::first());
    }

    /**
     * Check if Table-Seeder sets the default currency correctly.
     * @return void
     */
    public function test_has_one_default_currency()
    {
        $cur = Currency::where('is_default', 1)->count();
        $this->assertEquals($cur, 1);

        return $cur;
    }

    /**
     * Only one currency can be the default currency.
     * @return void
     */
    public function test_only_one_default_currency()
    {
        $oldDefaultCurrencyId = Currency::default()->id;

        // Set new currency
        $currency = Currency::where('is_default', 0)->first();
        $currency->is_default = true;
        $this->assertTrue($currency->save(), 'Currency could not be updated.');

        // Check if new currency is the default one
        $default = Currency::where('is_default', 1)->first();
        $this->assertNotEmpty($default);
        $this->assertEquals($default->id, $currency->id);
        $this->assertNotEquals($default->id, $oldDefaultCurrencyId);

        return $currency;
    }

    /**
     * Disabled currencies cannot be the default currency.
     * @return void
     */
    public function test_disabled_currencies_cannot_be_default()
    {
        $currency = Currency::where('is_default', 0)->first();

        // Disable currency
        $currency->is_enabled = false;
        $currency->save();

        // Try to set default on disabled currency, should do nothing.
        $currency->is_default = true;
        $currency->save();

        $sameCurrency = Currency::withDisabled()->where('id', $currency->id)->first();
        $this->assertNotEmpty($sameCurrency);
        $this->assertFalse($sameCurrency->is_default);

        return $currency;
    }

    /**
     * Default currencies cannot neither be disabled not unset as default.
     * @return void
     */
    public function test_disable_default_currency()
    {
        $oldCurrency = Currency::where('is_default', 1)->first();

        // Disable Currency and remove default state
        $oldCurrency->is_default = false;
        $oldCurrency->is_enabled = false;
        $oldCurrency->save();

        // Another currency should now the default cone.
        $newCurrency = Currency::where('is_default', 1)->first();
        $this->assertNotEmpty($newCurrency, 'No default currency available.');
        $this->assertNotEquals($oldCurrency->id, $newCurrency->id, 'No default currency available.');

        // Disable any other currency
        Currency::where('is_default', 0)->update(['is_enabled' => 0]);

        // Try to un-default only available currency, should throw an exception.
        $currency = Currency::where('is_default', 1)->first();
        $currency->is_default = false;

        if (method_exists($this, 'assertThrows')) {
            $this->assertThrows(fn () => $currency->save(), Throwable::class);
        } else {
            try {
                $value = $currency->save();
            } catch (Exception $exc) {
                $value = false;
            }
            $this->assertFalse($value);
        }

        // Try to disable only available currency, should throw an exception.
        $currency = Currency::where('is_default', 1)->first();
        $currency->is_default = false;

        if (method_exists($this, 'assertThrows')) {
            $this->assertThrows(fn () => $currency->save(), Throwable::class);
        } else {
            try {
                $value = $currency->save();
            } catch (Exception $exc) {
                $value = false;
            }
            $this->assertFalse($value);
        }
    }

    /**
     * Disabled currencies cannot be the default currency.
     * @return void
     */
    public function test_receive_disabled_currencies_when_requested()
    {
        $currency = Currency::where('is_default', 0)->first();
        $currency->is_enabled = false;
        $currency->save();

        $this->assertEquals(Currency::count(), 2);
        $this->assertEquals(Currency::withDisabled()->count(), 3);
    }
}
