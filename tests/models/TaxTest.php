<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Classes\User\Auth;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Tests\PluginTestCase;
use RainLab\User\Models\User;

class TaxTest extends PluginTestCase
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
     * Return only enabled taxes.
     * @return void
     */
    public function test_return_only_enabled_taxes()
    {
        Tax::first()->update(['is_enabled' => false]);
        $this->assertEquals(Tax::count(), 1);
    }

    /**
     * Hide disabled taxes on products, unless withDisabled is used on query.
     * @return void
     */
    public function test_hide_disabled_taxes_on_product_unless_with_disabled()
    {
        $tax = Tax::first();

        // Assign Tax to Product
        $product = Product::first();
        $product->taxes()->attach($tax->id);
        
        // Check if assigned
        $product = Product::first();
        $this->assertNotEmpty($product->taxes()->get()->toArray());

        // Disable Tax
        $tax->is_enabled = false;
        $tax->save();

        // Hide Tax
        $product = Product::first();
        $this->assertEmpty($product->taxes()->get()->toArray());
        $this->assertNotEmpty($product->taxes()->withDisabled()->get()->toArray());
    }

    /**
     * Hide disabled taxes on shipping methods, unless withDisabled is used on query.
     * @return void
     */
    public function test_hide_disabled_taxes_on_shipping_unless_with_disabled()
    {
        $tax = Tax::first();

        // Assign Tax to Product
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->taxes()->attach($tax->id);
        
        // Check if assigned
        $shippingMethod = ShippingMethod::first();
        $this->assertNotEmpty($shippingMethod->taxes()->get()->toArray());

        // Disable Tax
        $tax->is_enabled = false;
        $tax->save();

        // Hide Tax
        $shippingMethod = ShippingMethod::first();
        $this->assertEmpty($shippingMethod->taxes()->get()->toArray());
        $this->assertNotEmpty($shippingMethod->taxes()->withDisabled()->get()->toArray());
    }

    /**
     * Hide disabled taxes on payment methods, unless withDisabled is used on query.
     * @return void
     */
    public function test_hide_disabled_taxes_on_payment_unless_with_disabled()
    {
        $tax = Tax::first();

        // Assign Tax to Product
        $paymentMethod = PaymentMethod::first();
        $paymentMethod->taxes()->attach($tax->id);
        
        // Check if assigned
        $paymentMethod = PaymentMethod::first();
        $this->assertNotEmpty($paymentMethod->taxes()->get()->toArray());

        // Disable Tax
        $tax->is_enabled = false;
        $tax->save();

        // Hide Tax
        $paymentMethod = PaymentMethod::first();
        $this->assertEmpty($paymentMethod->taxes()->get()->toArray());
        $this->assertNotEmpty($paymentMethod->taxes()->withDisabled()->get()->toArray());
    }
}
