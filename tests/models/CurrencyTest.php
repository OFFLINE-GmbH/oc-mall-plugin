<?php declare(strict_types=1);

namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Classes\Customer\AuthManager;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\CustomFieldValue;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\OrderState;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Models\User;
use OFFLINE\Mall\Tests\PluginTestCase;
use RainLab\User\Facades\Auth;

class CurrencyTest extends PluginTestCase
{
    /**
     * Setup the test environment.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        app()->singleton('user.auth', function () {
            return AuthManager::instance();
        });
        Auth::login(User::first());
    }

    /**
     * Check if Table-Seeder sets default currency correctly.
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
    public function test_disabled_currencies_cannot_be_default()
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
    public function test_disable_default_currency()
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
