<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Classes\Customer\AuthManager;
use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Classes\PaymentState\PendingState;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\CustomFieldValue;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\OrderState;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Models\User;
use OFFLINE\Mall\Models\Variant;
use OFFLINE\Mall\Tests\PluginTestCase;
use RainLab\User\Facades\Auth;

class OrderTest extends PluginTestCase
{
    public function setUp()
    {
        parent::setUp();

        app()->singleton('user.auth', function () {
            return AuthManager::instance();
        });
        Auth::login(User::first());
    }

    public function test_it_creates_a_new_order_from_a_cart()
    {
        $cart                      = $this->getFullCart();
        $cart->shipping_address_id = 2;
        $cart->billing_address_id  = 1;
        $cart->save();

        $order = Order::fromCart($cart);
        $order->save();

        $this->assertEquals(1, $order->order_number);
        $this->assertEquals(PendingState::class, $order->payment_state);
        $this->assertEquals(OrderState::where('flag', OrderState::FLAG_NEW)->first()->id, $order->order_state_id);
        $this->assertEquals(76.92, $order->total_shipping_pre_taxes);
        $this->assertEquals(23.08, $order->total_shipping_taxes);
        $this->assertEquals(100.00, $order->total_shipping_post_taxes);
        $this->assertEquals(923.08, $order->total_product_pre_taxes);
        $this->assertEquals(1200.00, $order->total_product_post_taxes);

        $this->assertEquals(1000.00, $order->total_pre_taxes);
        $this->assertEquals(300.00, $order->total_taxes);
        $this->assertEquals(1300.00, $order->total_post_taxes);
        $this->assertEquals(2800, $order->total_weight);
        $this->assertNotEmpty($order->ip_address);

        $this->assertFalse($order->shipping_address_same_as_billing);
        $this->assertEquals(json_encode(Address::find(1)), $order->getOriginal('billing_address'));
        $this->assertEquals(json_encode(Address::find(2)), $order->getOriginal('shipping_address'));

        $this->assertNotNull($cart->deleted_at);
    }

    public function test_it_updates_product_stock()
    {
        $product        = Product::first();
        $product->price = ['CHF' => 200, 'EUR' => 300];
        $product->stock = 10;
        $product->save();

        $cart = $this->getSimpleCart();
        $cart->addProduct($product, 2);

        $order = Order::fromCart($cart);
        $order->save();

        $this->assertEquals(8, $product->fresh()->stock);
    }

    public function test_it_prevents_out_of_stock_purchase()
    {
        $this->expectException(OutOfStockException::class);

        $product                               = Product::first();
        $product->price                        = ['CHF' => 200, 'EUR' => 300];
        $product->stock                        = 10;
        $product->allow_out_of_stock_purchases = false;
        $product->save();

        $cart = $this->getSimpleCart();
        $cart->addProduct($product, 12);

        $order = Order::fromCart($cart);
        $order->save();

        $this->assertEquals(10, $product->fresh()->stock);
    }

    public function test_it_allows_explicit_out_of_stock_purchase()
    {
        $product                               = Product::first();
        $product->price                        = ['CHF' => 200, 'EUR' => 300];
        $product->stock                        = 10;
        $product->allow_out_of_stock_purchases = true;
        $product->save();

        $cart = $this->getSimpleCart();
        $cart->addProduct($product, 12);

        $order = Order::fromCart($cart);
        $order->save();

        $this->assertEquals(-2, $product->fresh()->stock);
    }

    public function test_it_updates_variant_stock()
    {
        $product        = Product::first();
        $product->price = ['CHF' => 200, 'EUR' => 300];
        $product->stock = 10;
        $product->save();

        $variant             = new Variant();
        $variant->name       = 'Variant';
        $variant->product_id = $product->id;
        $variant->stock      = 20;
        $variant->save();

        $cart = $this->getSimpleCart();
        $cart->addProduct($product, 2, $variant);

        $order = Order::fromCart($cart);
        $order->save();

        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertEquals(18, $variant->fresh()->stock);
    }

    public function test_it_prevents_out_of_stock_variant_purchase()
    {
        $this->expectException(OutOfStockException::class);

        $product        = Product::first();
        $product->price = ['CHF' => 200, 'EUR' => 300];
        $product->stock = 10;
        $product->save();

        $variant                               = new Variant();
        $variant->name                         = 'Variant';
        $variant->product_id                   = $product->id;
        $variant->stock                        = 20;
        $product->allow_out_of_stock_purchases = false;
        $variant->save();

        $cart = $this->getSimpleCart();
        $cart->addProduct($product, 21, $variant);

        $order = Order::fromCart($cart);
        $order->save();

        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertEquals(20, $variant->fresh()->stock);
    }

    public function test_it_allows_explicit_out_of_stock_variant_purchase()
    {
        $this->expectException(OutOfStockException::class);

        $product        = Product::first();
        $product->price = ['CHF' => 200, 'EUR' => 300];
        $product->stock = 10;
        $product->save();

        $variant                               = new Variant();
        $variant->name                         = 'Variant';
        $variant->product_id                   = $product->id;
        $variant->stock                        = 20;
        $product->allow_out_of_stock_purchases = true;
        $variant->save();

        $cart = $this->getSimpleCart();
        $cart->addProduct($product, 21, $variant);

        $order = Order::fromCart($cart);
        $order->save();

        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertEquals(-1, $variant->fresh()->stock);
    }

    public function test_it_uses_the_correct_discounted_shipping_method()
    {
        $cart   = $this->getSimpleCart(true);
        $method = ShippingMethod::find($cart->shipping_method->id);

        $this->assertEquals(1, $method->id);
        $this->assertEquals('Default', $method->name);

        $discount                       = new Discount();
        $discount->name                 = 'Shipping Test';
        $discount->type                 = 'shipping';
        $discount->trigger              = 'code';
        $discount->code                 = 'SHIPPING';
        $discount->shipping_description = 'Reduced shipping';
        $discount->save();

        $discount->shipping_prices()->save(new Price([
            'currency_id' => 1,
            'price'       => 10,
            'field'       => 'shipping_prices',
        ]));

        $cart->applyDiscount($discount);

        $order = Order::fromCart($cart);
        $this->assertEquals($discount->shipping_description, $order->shipping['method']['name']);
        $this->assertEquals($discount->shippingPrice()->integer, $order->shipping['total']);
        $this->assertEquals(
            $discount->shippingPrice()->integer,
            $order->shipping['method']['price']['CHF']['price']
        );
    }

    public function test_discount_number_of_usages_gets_updated()
    {
        $cart                            = $this->getSimpleCart(true);
        $discount1                       = new Discount();
        $discount1->name                 = 'Shipping Test';
        $discount1->type                 = 'shipping';
        $discount1->trigger              = 'total';
        $discount1->shipping_description = 'Reduced shipping';
        $discount1->number_of_usages     = 10;
        $discount1->save();

        $discount1->shipping_prices()->save(new Price([
            'currency_id' => 1,
            'price'       => 10,
            'field'       => 'shipping_price',
        ]));
        $discount1->totals_to_reach()->save(new Price([
            'currency_id' => 1,
            'price'       => 0,
            'field'       => 'total_to_reach',
        ]));

        $discount2                   = new Discount();
        $discount2->name             = 'Amount Test';
        $discount2->type             = 'fixed_amount';
        $discount2->trigger          = 'code';
        $discount2->code             = 'TEST';
        $discount2->number_of_usages = 12;
        $discount2->save();

        $discount2->amounts()->save(new Price([
            'currency_id' => 1,
            'price'       => 20,
            'field'       => 'amount',
        ]));

        $discount3                   = new Discount();
        $discount3->name             = 'Amount Test';
        $discount3->type             = 'rate';
        $discount3->rate             = 20;
        $discount3->trigger          = 'code';
        $discount3->number_of_usages = 18;
        $discount3->save();

        $cart->applyDiscount($discount2);
        $cart->applyDiscount($discount3);

        Order::fromCart($cart);

        $this->assertEquals(11, $discount1->fresh()->number_of_usages);
        $this->assertEquals(13, $discount2->fresh()->number_of_usages);
        $this->assertEquals(19, $discount3->fresh()->number_of_usages);
    }

    protected function getFullCart(): Cart
    {
        $tax1             = new Tax();
        $tax1->name       = 'Tax 1';
        $tax1->percentage = 10;
        $tax1->save();
        $tax2             = new Tax();
        $tax2->name       = 'Tax 2';
        $tax2->percentage = 20;
        $tax2->save();

        $productA                     = Product::first();
        $productA->stackable          = true;
        $productA->weight             = 400;
        $productA->stock              = 10;
        $productA->price_includes_tax = true;
        $productA->save();
        $productA->price = ['CHF' => 200, 'EUR' => 300];
        $productA->taxes()->attach([$tax1->id, $tax2->id]);
        $productA = Product::find($productA->id);

        $productB                     = new Product;
        $productB->name               = 'Another Product';
        $productB->stock              = 10;
        $productB->weight             = 800;
        $productB->price_includes_tax = true;
        $productB->save();
        $productB->price = ['CHF' => 100, 'EUR' => 300];
        $productB->taxes()->attach([$tax1->id, $tax2->id]);
        $productB = Product::find($productB->id);

        $sizeA             = new CustomFieldOption();
        $sizeA->name       = 'Size A';
        $sizeA->sort_order = 1;
        $sizeA->save();
        $sizeA->prices()->save(new Price([
            'price'       => 100,
            'currency_id' => 1,
        ]));

        $sizeB             = new CustomFieldOption();
        $sizeB->name       = 'Size B';
        $sizeB->sort_order = 1;
        $sizeB->save();
        $sizeB->prices()->save(new Price([
            'price'       => 200,
            'currency_id' => 1,
        ]));

        $field       = new CustomField();
        $field->name = 'Size';
        $field->type = 'dropdown';
        $field->save();

        $field->custom_field_options()->save($sizeA);
        $field->custom_field_options()->save($sizeB);

        $productA->custom_fields()->attach($field);

        $customFieldValueA                         = new CustomFieldValue();
        $customFieldValueA->custom_field_id        = $field->id;
        $customFieldValueA->custom_field_option_id = $sizeA->id;

        $customFieldValueB                         = new CustomFieldValue();
        $customFieldValueB->custom_field_id        = $field->id;
        $customFieldValueB->custom_field_option_id = $sizeB->id;

        $cart = new Cart();
        $cart->addProduct($productA, 2, null, collect([$customFieldValueA]));
        $cart->addProduct($productA, 1, null, collect([$customFieldValueB]));
        $cart->addProduct($productB, 2);

        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 300];

        $shippingMethod->taxes()->attach([$tax1->id, $tax2->id]);

        $cart->setShippingMethod($shippingMethod);

        $cart->setCustomer(Customer::first());

        $cart->setBillingAddress(Address::find(1));
        $cart->setShippingAddress(Address::find(2));

        return $cart;
    }

    protected function getSimpleCart($withProduct = false): Cart
    {
        $cart = new Cart();
        if ($withProduct) {
            $product                     = Product::first();
            $product->stackable          = true;
            $product->weight             = 400;
            $product->stock              = 10;
            $product->price_includes_tax = true;
            $product->save();
            $product->price = ['CHF' => 200, 'EUR' => 300];

            $product = Product::first();

            $cart->addProduct($product, 2);
        }

        $cart->setShippingMethod(ShippingMethod::first());
        $cart->setCustomer(Customer::first());
        $cart->setBillingAddress(Address::find(1));
        $cart->setShippingAddress(Address::find(2));

        return $cart;
    }
}
