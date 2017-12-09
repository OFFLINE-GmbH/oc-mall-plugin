<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Classes\OrderStatus\InProgressState;
use OFFLINE\Mall\Classes\PaymentStatus\PendingState;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\CustomFieldValue;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Models\Variant;
use PluginTestCase;

class OrderTest extends PluginTestCase
{
    public function test_it_creates_a_new_order_from_a_cart()
    {
        $order = Order::fromCart($this->getCart());
        $order->save();

        $this->assertEquals(1, $order->order_number);
        $this->assertEquals(PendingState::class, $order->payment_status);
        $this->assertEquals(InProgressState::class, $order->order_status);
        $this->assertEquals(9000, $order->shipping_pre_taxes);
        $this->assertEquals(1000, $order->shipping_taxes);
        $this->assertEquals(10000, $order->total_shipping);
        $this->assertEquals(27691, $order->product_taxes);
        $this->assertEquals(92309, $order->total_product);
        $this->assertEquals(101309, $order->total_pre_taxes);
        $this->assertEquals(28691, $order->total_taxes);
        $this->assertEquals(130000, $order->total_post_taxes);
        $this->assertEquals(2800, $order->total_weight);
        $this->assertNotEmpty($order->ip_address);

        $this->assertFalse($order->shipping_address_same_as_billing);
        $this->assertEquals(json_encode(Address::find(1)), $order->getOriginal('billing_address'));
        $this->assertEquals(json_encode(Address::find(2)), $order->getOriginal('shipping_address'));
    }

    protected function getCart(): Cart
    {
        $tax1             = new Tax();
        $tax1->name       = 'Tax 1';
        $tax1->percentage = 10;
        $tax1->save();
        $tax2             = new Tax();
        $tax2->name       = 'Tax 2';
        $tax2->percentage = 20;
        $tax2->save();

        $productA            = Product::first();
        $productA->stackable = true;
        $productA->price     = 200;
        $productA->weight    = 400;
        $productA->save();
        $productA->taxes()->attach([$tax1->id, $tax2->id]);

        $productB         = new Product;
        $productB->name   = 'Another Product';
        $productB->price  = 100;
        $productB->weight = 800;
        $productB->save();
        $productB->taxes()->attach([$tax1->id, $tax2->id]);

        $sizeA             = new CustomFieldOption();
        $sizeA->name       = 'Size A';
        $sizeA->price      = 100;
        $sizeA->sort_order = 1;
        $sizeB             = new CustomFieldOption();
        $sizeB->name       = 'Size B';
        $sizeB->price      = 200;
        $sizeB->sort_order = 1;

        $field             = new CustomField();
        $field->name       = 'Size';
        $field->type       = 'dropdown';
        $field->product_id = $productA->id;
        $field->save();

        $field->options()->save($sizeA);
        $field->options()->save($sizeB);

        $variant             = new Variant();
        $variant->product_id = $productA->id;
        $variant->stock      = 1;
        $variant->save();

        $variant->custom_field_options()->attach($sizeA);
        $variant->custom_field_options()->attach($sizeB);

        $customFieldValueA                         = new CustomFieldValue();
        $customFieldValueA->custom_field_id        = $field->id;
        $customFieldValueA->custom_field_option_id = $sizeA->id;
        $customFieldValueB                         = new CustomFieldValue();
        $customFieldValueB->custom_field_id        = $field->id;
        $customFieldValueB->custom_field_option_id = $sizeB->id;

        $cart = new Cart();
        $cart->addProduct($productA, 2, $customFieldValueA);
        $cart->addProduct($productA, 1, $customFieldValueB);
        $cart->addProduct($productB, 2);

        $shippingMethod        = ShippingMethod::first();
        $shippingMethod->price = 100;
        $shippingMethod->save();

        $shippingMethod->taxes()->attach($tax1);

        $cart->setShippingMethod($shippingMethod);

        $cart->setCustomer(Customer::first());

        $cart->setBillingAddress(Address::find(1));
        $cart->setShippingAddress(Address::find(2));

        return $cart;
    }
}
