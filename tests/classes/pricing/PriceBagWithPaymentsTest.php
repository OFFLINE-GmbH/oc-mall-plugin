<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Tests\Classes\Pricing;

use OFFLINE\Mall\Classes\Pricing\PriceBag;
use OFFLINE\Mall\Models\PaymentMethod;

class PriceBagWithPaymentsTest extends BasePriceBagTestCase
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
     * Test if the fixed payment fee is calculated and added to the totals correctly.
     * @return void
     */
    public function test_calculate_fixed_payment_fee()
    {
        $product = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->save();

        // Create Payment Method
        $payment = PaymentMethod::first();
        $payment->price = ['CHF' => 10, 'EUR' => 15];

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 1);
        $cart->setPaymentMethod($payment);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            1000,
            $bag->paymentFee()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            11000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test the the taxes from a fixed payment fee are calculated correctly.
     * @return void
     */
    public function test_calculate_fixed_payment_fee_tax()
    {
        $tax1 = $this->getTax('Test 1', 10);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->save();

        // Create Payment Method
        $payment = PaymentMethod::first();
        $payment->price = ['CHF' => 10, 'EUR' => 15];
        $payment->taxes()->attach($tax1);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 1);
        $cart->setPaymentMethod($payment);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            100,
            $bag->paymentTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            1100,
            $bag->paymentFee()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            11100,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if the percentage payment fee is calculated from the totals (incl. shipping) and added
     * to the totals correctly.
     * @return void
     */
    public function test_calculate_rate_payment_fee()
    {
        $product = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->save();

        // Create Payment Method
        $payment = PaymentMethod::first();
        $payment->fee_percentage = 10;
        $payment->save();

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 1);
        $cart->setPaymentMethod($payment);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            1000,
            $bag->paymentFee()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            11000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test the the taxes from a percentage payment fee are calculated correctly.
     * @return void
     */
    public function test_calculate_rate_payment_fee_tax()
    {
        $tax1 = $this->getTax('Test 1', 10);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->save();

        // Create Payment Method
        $payment = PaymentMethod::first();
        $payment->fee_percentage = 10;
        $payment->save();
        $payment->taxes()->attach($tax1);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 1);
        $cart->setPaymentMethod($payment);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            100,
            $bag->paymentTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            1100,
            $bag->paymentFee()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            11100,
            $bag->totalInclusive()->toInt()
        );
    }
}
