<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Tests\Classes\Pricing;

use OFFLINE\Mall\Classes\Pricing\PriceBag;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\Variant;

class PriceBagWithDiscountsTest extends BasePriceBagTestCase
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
     * Test if the PriceBag calculates different taxes correctly after discount has been applied.
     *
     * Results
     *      tax 1       =   7.69        (10 % of 200.00 - 100.00 discount) <- prices are inclusive
     *      tax 2       =  15.38        (20 % of 200.00 - 100.00 discount) <- prices are inclusive
     *      taxes       =  23.07
     *      inclusive   = 200.00
     *
     * @return void
     */
    public function test_calculate_taxes_correctly_after_discount_applied()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->stock = 10;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        // Create Discount
        $discount = Discount::create([
            'code' => 'Test',
            'trigger' => 'code',
            'name' => 'Test discount',
            'type' => 'fixed_amount',
        ]);
        $discount->amounts()->save(new Price([
            'price'       => 100,
            'currency_id' => 2,
            'field'       => 'amounts',
        ]));

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);
        $cart->applyDiscount($discount);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            769,
            $bag->productsTaxes()[0]['taxes'][0]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            1538,
            $bag->productsTaxes()[0]['taxes'][1]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            2307,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            10000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if the PriceBag calculates correctly after discount has been applied resulting in 0 amount.
     *
     * Results
     *      tax 1       = 0.00        (10 %)
     *      tax 2       = 0.00        (20 %)
     *      taxes       = 0.00
     *      inclusive   = 0.00
     *
     * @return void
     */
    public function test_calculate_correctly_after_discount_results_in_zero_amount()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->stock = 10;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        // Create Discount
        $discount = Discount::create([
            'code'    => 'Test',
            'trigger' => 'code',
            'name'    => 'Test discount',
            'type'    => 'rate',
            'rate'    => 100,
        ]);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);
        $cart->applyDiscount($discount);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            0,
            $bag->productsTaxes()[0]['taxes'][0]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            0,
            $bag->productsTaxes()[0]['taxes'][1]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            0,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            0,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if the PriceBag calculates correctly using multiple products with different taxes and
     * applied discounts.
     *
     * Results
     *      tax 1       =   7.09        (10 % of 115.00 - 66.66 % of 50.00 discount) <- prices are inclusive
     *      tax 2       =   3.54        ( 5 % of 115.00 - 66.66 % of 50.00 discount) <- prices are inclusive
     *      tax 3       =   5.35        (15 % of  57.50 - 33.33 % of 50.00 discount) <- prices are inclusive
     *      taxes       =  15.98
     *      inclusive   = 122.50
     *
     * @return void
     */
    public function test_calculate_multiple_products_with_different_taxes_and_discount()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 5);
        $tax3 = $this->getTax('Test 3', 15);

        // Create Products
        $product1 = $this->getProduct(115);
        $product1->price_includes_tax = true;
        $product1->stock = 10;
        $product1->taxes()->attach([$tax1->id, $tax2->id]);
        $product1->save();

        $product2 = $this->getProduct(57.50);
        $product2->price_includes_tax = true;
        $product2->stock = 10;
        $product2->taxes()->attach([$tax3->id]);
        $product2->save();

        // Create Discount
        $discount = Discount::create([
            'code'    => 'Test',
            'trigger' => 'code',
            'name'    => 'Test discount',
            'type'    => 'fixed_amount',
        ]);
        $discount->amounts()->save(new Price([
            'price'       => 50,
            'currency_id' => 2,
            'field'       => 'amounts',
        ]));

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product1, 1);
        $cart->addProduct($product2, 1);
        $cart->applyDiscount($discount);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            709,
            $bag->productsTaxes()[0]['taxes'][0]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            354,
            $bag->productsTaxes()[0]['taxes'][1]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            535,
            $bag->productsTaxes()[1]['vat']->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            1598,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            12250,
            $bag->totalInclusive()->toInt()
        );
    }
   
    /**
     * Test if the PriceBag calculates different taxes correctly after discount has been applied.
     *
     * Results
     *      tax 1       =   8.00        (10 % of 160.00 - 100.00 discount)
     *      tax 2       =  16.00        (20 % of 160.00 - 100.00 discount)
     *      taxes       =  24.00
     *      inclusive   =  78.00
     *
     * @return void
     */
    public function test_calculate_excluded_taxes_correctly_after_discount_applied()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        // Create Product
        $product = $this->getProduct(80);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        // Create Discount
        $discount = Discount::create([
            'code'    => 'Test',
            'trigger' => 'code',
            'name'    => 'Test discount',
            'type'    => 'fixed_amount',
        ]);
        $discount->amounts()->save(new Price([
            'price'       => 100,
            'currency_id' => 2,
            'field'       => 'amounts',
        ]));

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);
        $cart->applyDiscount($discount);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            600,
            $bag->productsTaxes()[0]['taxes'][0]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            1200,
            $bag->productsTaxes()[0]['taxes'][1]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            1800,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            7800,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if fixed discount is applied and calculated correctly.
     * @return void
     */
    public function test_calculate_fixed_discount()
    {
        $price = ['CHF' => 20000, 'EUR' => 24000];
        $quantity = 5;

        // Create Discount
        $discount = Discount::create([
            'code'    => 'Test',
            'trigger' => 'code',
            'name'    => 'Test discount',
            'type'    => 'fixed_amount',
        ]);
        $discount->amounts()->save(new Price([
            'price'       => 100,
            'currency_id' => 2,
            'field'       => 'amounts',
        ]));

        // Get Cart
        $cart = $this->getCart();
        $cart->addProduct($this->getProduct($price), $quantity);
        $cart->applyDiscount($discount);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            ($quantity * $price['CHF'] * 100) - 10000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if rate discount is applied and calculated correctly.
     * @return void
     */
    public function test_calculate_rate_discount()
    {
        $price = ['CHF' => 20000, 'EUR' => 24000];
        $quantity = 5;

        // Create Discount
        $discount = Discount::create([
            'code'    => 'Test',
            'name'    => 'Test discount',
            'trigger' => 'code',
            'type'    => 'rate',
            'rate'    => 50,
        ]);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($this->getProduct($price), $quantity);
        $cart->applyDiscount($discount);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            ($quantity * $price['CHF'] * 100) / 2,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if rate discount is applied and calculated always on the base price.
     * @return void
     */
    public function test_calculate_rate_discount_always_on_base_price()
    {
        $price = ['CHF' => 20000, 'EUR' => 24000];
        $quantity = 5;

        // Create Discounts
        $discountA = Discount::create([
            'name'    => 'Test discount',
            'trigger' => 'code',
            'type'    => 'rate',
            'rate'    => 25,
        ]);

        $discountB       = $discountA->replicate();
        $discountB->code = 'xxx';
        $discountB->save();

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($this->getProduct($price), $quantity);
        $cart->applyDiscount($discountA);
        $cart->applyDiscount($discountB);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            ($quantity * $price['CHF'] * 100) / 2,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if alternate shipping price is calculated correctly.
     * @return void
     */
    public function test_calculate_alternate_shipping_price_discount()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        // Create Shipping Method
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->prices()->save(new Price([
            'price'       => 200,
            'currency_id' => 2,
        ]));
        $shippingMethod->taxes()->attach($tax1);

        // Create Discount
        $discount = Discount::create([
            'code'                 => 'Test',
            'name'                 => 'Test discount',
            'trigger'              => 'code',
            'type'                 => 'shipping',
            'shipping_description' => 'Test shipping',
        ]);
        $discount->shipping_prices()->save(new Price([
            'price'       => 100,
            'currency_id' => 2,
            'field'       => 'shipping_prices',
        ]));

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);
        $cart->setShippingMethod($shippingMethod);
        $cart->applyDiscount($discount);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            5524,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            30000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if fixed discount is applied and calculated only when given total is reached.
     * @return void
     */
    public function test_calculate_fixed_discount_only_when_total_is_reached()
    {
        $product = $this->getProduct(100);

        // Create Discount
        $discount = Discount::create([
            'code'    => 'Test',
            'name'    => 'Test discount',
            'type'    => 'fixed_amount',
            'trigger' => 'total',
        ]);
        $discount->totals_to_reach()->save(new Price([
            'price'       => 300,
            'currency_id' => 2,
            'field'       => 'totals_to_reach',
        ]));
        $discount->amounts()->save(new Price([
            'price'       => 150,
            'currency_id' => 2,
            'field'       => 'amounts',
        ]));

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);
        $cart->applyDiscount($discount);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            20000,
            $bag->totalInclusive()->toInt()
        );

        // Add Another Product
        $cart->addProduct($product);
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            15000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if rate discount is applied and calculated only when given total is reached.
     * @return void
     */
    public function test_calculate_rate_discount_only_when_total_is_reached()
    {
        $product = $this->getProduct(100);

        // Create Discount
        $discount = Discount::create([
            'code'    => 'Test',
            'name'    => 'Test discount',
            'type'    => 'rate',
            'rate'    => 50,
            'trigger' => 'total',
        ]);
        $discount->totals_to_reach()->save(new Price([
            'price'       => 300,
            'currency_id' => 2,
            'field'       => 'totals_to_reach',
        ]));

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);
        $cart->applyDiscount($discount);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            20000,
            $bag->totalInclusive()->toInt()
        );

        // Add Another Product
        $cart->addProduct($product);
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            15000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if alternate shipping price is calculated only when total is reached.
     * @return void
     */
    public function test_calculate_alternate_shipping_price_discount_only_when_total_is_reached()
    {
        // Create Product
        $product = $this->getProduct(100);

        // Create Shipping Method
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->price = ['CHF' => 200, 'EUR' => 150];

        // Create Discount
        $discount = Discount::create([
            'code'                 => 'Test',
            'name'                 => 'Test discount',
            'trigger'              => 'total',
            'type'                 => 'shipping',
            'shipping_description' => 'Test shipping',
        ]);
        $discount->totals_to_reach()->save(new Price([
            'price'       => 300,
            'currency_id' => 2,
            'field'       => 'totals_to_reach',
        ]));
        $discount->shipping_prices()->save(new Price([
            'price'       => 0,
            'currency_id' => 2,
            'field'       => 'shipping_prices',
        ]));

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);
        $cart->setShippingMethod($shippingMethod);
        $cart->applyDiscount($discount);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            40000,
            $bag->totalInclusive()->toInt()
        );

        // Add Another Product
        $cart->addProduct($product);
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            30000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if fixed discount is applied and calculated only when a specific product is in cart.
     * @return void
     */
    public function test_calculate_fixed_discount_only_when_product_is_in_cart()
    {
        // Create Products
        $productA = $this->getProduct(100);
        $productB = $this->getProduct(100);

        // Create Discount
        $discount = Discount::create([
            'code'       => 'xxxx',
            'name'       => 'Test discount',
            'type'       => 'fixed_amount',
            'trigger'    => 'product',
            'product_id' => $productB->id,
        ]);
        $discount->amounts()->save(new Price([
            'price'       => 150,
            'currency_id' => 2,
            'field'       => 'amounts',
        ]));

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($productA, 2);
        $cart->applyDiscount($discount);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            20000,
            $bag->totalInclusive()->toInt()
        );

        // Add Another Product
        $cart->addProduct($productB);
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            15000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if rate discount is applied and calculated only when a specific product is in cart.
     * @return void
     */
    public function test_calculate_rate_discount_only_when_product_is_in_cart()
    {
        $productA = $this->getProduct(100);
        $productB = $this->getProduct(100);

        // Create Discount
        $discount = Discount::create([
            'code'       => 'xxxx',
            'name'       => 'Test discount',
            'type'       => 'rate',
            'rate'       => 50,
            'trigger'    => 'product',
            'product_id' => $productB->id,
        ]);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($productA, 2);
        $cart->applyDiscount($discount);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            20000,
            $bag->totalInclusive()->toInt()
        );

        // Add Another Product
        $cart->addProduct($productB);
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            15000,
            $bag->totalInclusive()->toInt()
        );
    }

    /**
     * Test if alternate shipping price is calculated only when a specific product is in cart.
     * @return void
     */
    public function test_calculate_alternate_shipping_price_discount_only_when_product_is_in_cart()
    {
        $productA = $this->getProduct(100);
        $productB = $this->getProduct(100);

        // Create Variant
        $variant = Variant::create([
            'name'       => 'Variant',
            'product_id' => $productA->id,
            'price'      => ['CHF' => 100, 'EUR' => 150],
            'stock'      => 20,
        ]);

        // Create Shipping Method
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->price = ['CHF' => 200, 'EUR' => 150];

        // Create Discount
        $discount = Discount::create([
            'code'                 => 'Test',
            'name'                 => 'Test discount',
            'trigger'              => 'product',
            'type'                 => 'shipping',
            'shipping_description' => 'Test shipping',
            'product_id'           => $productB->id,
        ]);
        $discount->shipping_prices()->save(new Price([
            'price'       => 0,
            'currency_id' => 2,
            'field'       => 'shipping_prices',
        ]));

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($productA, 2, $variant);
        $cart->setShippingMethod($shippingMethod);
        $cart->applyDiscount($discount);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            40000,
            $bag->totalInclusive()->toInt()
        );

        // Add Another Product
        $cart->addProduct($productB);
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            30000,
            $bag->totalInclusive()->toInt()
        );
    }
}
