<?php declare(strict_types=1);

namespace OFFLINE\Mall\Tests\Classes\Pricing;

use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Tests\PluginTestCase;

abstract class BasePriceBagTestCase extends PluginTestCase
{
    /**
     * Shipping Address
     * @var Address
     */
    protected $address;

    /**
     * Setup the test environment.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->address = Address::factory()->create();
    }

    /**
     * Get generic product for testing.
     * @param mixed $price
     * @return Product
     */
    protected function getProduct($price)
    {
        if (is_int($price) || is_float($price)) {
            $price = ['CHF' => $price, 'EUR' => $price];
        }

        $product = Product::first()->replicate(['category_id']);
        $product->save();
        $product->price = $price;

        return Product::find($product->id);
    }

    /**
     * Get generic cart for testing.
     * @param mixed $price
     * @return Cart
     */
    protected function getCart(): Cart
    {
        $cart                      = new Cart();
        $cart->shipping_address_id = $this->address->id;
        $cart->save();
        return $cart;
    }

    /**
     * Get generic tax for testing.
     * @param mixed $price
     * @return Tax
     */
    protected function getTax($name, int $percentage): Tax
    {
        $tax1             = new Tax();
        $tax1->name       = $name;
        $tax1->percentage = $percentage;
        $tax1->save();

        return $tax1;
    }
}
