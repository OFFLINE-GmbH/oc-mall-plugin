<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Product;
use PluginTestCase;

class CartTest extends PluginTestCase
{
    public function test_it_stacks_products()
    {
        $product            = Product::first();
        $product->stackable = true;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product);

        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->pivot->quantity);

        $cart->addProduct($product);
        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(2, $cart->products->first()->pivot->quantity);
    }

    public function test_it_doesnt_stack_products()
    {
        $product            = Product::first();
        $product->stackable = false;
        $product->save();

        $cart = new Cart();
        $cart->addProduct($product);

        $this->assertEquals(1, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->pivot->quantity);

        $cart->addProduct($product);
        $this->assertEquals(2, $cart->products->count());
        $this->assertEquals(1, $cart->products->first()->pivot->quantity);
    }
}
