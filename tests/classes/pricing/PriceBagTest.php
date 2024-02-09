<?php declare(strict_types=1);

namespace OFFLINE\Mall\Tests\Classes\Pricing;

use OFFLINE\Mall\Classes\Pricing\PriceBag;
use OFFLINE\Mall\Classes\Totals\TotalsCalculator;
use OFFLINE\Mall\Classes\Totals\TotalsCalculatorInput;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\CustomFieldValue;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Service;
use OFFLINE\Mall\Models\ServiceOption;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\ShippingMethodRate;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Models\Variant;
use OFFLINE\Mall\Tests\PluginTestCase;

class PriceBagTest extends PluginTestCase
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

    /**
     * Test if the PriceBag calculates the price correctly for a single product.
     * 
     * Results
     *      exclusive   = 1000.00
     *      inclusive   = 1000.00
     * 
     * @return void
     */
    public function test_calculate_price_for_single_product()
    {
        $price    = ['CHF' => 200, 'EUR' => 240];
        $product  = $this->getProduct($price);
        $quantity = 5;

        // Create Cart
        $cart = $this->getCart($product);
        $cart->addProduct($product, $quantity);

        // Create Bag
        $bag = PriceBag::fromCart($cart);

        // Check if price matches
        $this->assertEquals(
            intval($quantity * $price['CHF']) * 100, 
            $bag->totalExclusive()->integer()
        );
        $this->assertEquals(
            intval($quantity * $price['CHF']) * 100, 
            $bag->totalInclusive()->integer()
        );
    }

    /**
     * Test if the PriceBag calculates the price correctly for a single product with service option
     * and an additional tax applied.
     * 
     * Results
     *      exclusive   = 545.46
     *      tax         =  54.54
     *      inclusive   = 600.00
     * 
     * @return void
     */
    public function test_calculate_price_for_single_product_with_service_option()
    {
        $tax = $this->getTax('Test 1', 10);
        $price = ['CHF' => 200, 'EUR' => 240];
        $quantity = 2;
        
        // Create Service
        $service = Service::create(['name' => 'Test']);
        $service->taxes()->attach($tax->id);

        $option = ServiceOption::create([
            'name' => 'Test Option', 
            'service_id' => $service->id
        ]);
        $option->prices()->save(new Price([
            'currency_id' => 2,
            'price'       => 100,
        ]));

        // Create Product
        $product = $this->getProduct($price);
        $product->price_includes_tax = true;
        $product->save();
        $product->taxes()->attach($tax->id);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, $quantity, null, null, [$option->id]);
        $cart->reloadRelations('products');

        // Create Bag
        $bag = PriceBag::fromCart($cart);

        // Check
        $this->assertEquals(
            54546,
            $bag->totalExclusive()->integer()
        );
        $this->assertEquals(
            5454, 
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            60000, 
            $bag->totalInclusive()->integer()
        );
    }

    /**
     * Test if the PriceBag calculates the price correctly for a single product with service option
     * using 2 different taxes applied.
     * 
     * Results
     *      exclusive   = 488.64
     *      tax         = 111.36
     *      inclusive   = 600.00
     * 
     * @return void
     */
    public function test_calculate_price_using_multiple_taxes_on_service_option()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 50);
        $price = ['CHF' => 200, 'EUR' => 240];
        $quantity = 2;

        // Create Service
        $service = Service::create(['name' => 'Test']);
        $service->taxes()->attach([$tax1->id, $tax2->id]);

        // Create Options
        $option = ServiceOption::create(['name' => 'Test Option', 'service_id' => $service->id]);
        $option->prices()->save(new Price([
            'currency_id' => 2,
            'price'       => 100,
        ]));

        // Create Product
        $product = $this->getProduct($price);
        $product->price_includes_tax = true;
        $product->save();
        $product->taxes()->attach($tax1->id);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, $quantity, null, null, [$option->id]);
        $cart->reloadRelations('products');

        // Create Bag
        $bag = PriceBag::fromCart($cart);

        // Check
        $this->assertEquals(
            48864, 
            $bag->totalExclusive()->integer()
        );
        $this->assertEquals(
            11136, 
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            60000, 
            $bag->totalInclusive()->integer()
        );
    }

    /**
     * Test if the PriceBag calculates the price correctly for multiple products.
     * 
     * Results
     *      inclusive   = 2000.00
     * 
     * @return void
     */
    public function test_calculate_price_on_multiple_products()
    {
        $price = ['CHF' => 200, 'EUR' => 240];
        $halfPrice = ['CHF' => 100, 'EUR' => 120];
        $quantity = 5;

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($this->getProduct($price), $quantity);
        $cart->addProduct($this->getProduct($halfPrice), $quantity * 2);

        // Create Bag
        $bag = PriceBag::fromCart($cart);

        // Check
        $this->assertEquals(
            (($quantity * $price['CHF']) + ($quantity * 2 * $halfPrice['CHF'])) * 100,
            $bag->totalInclusive()->integer()
        );
    }

    /**
     * Test if the PriceBag calculates different taxes correctly.
     * 
     * Results
     *      exclusive   = 153.84
     *      no. taxes   = 2
     *      tax 1       =  15.38        (10 %)
     *      tax 2       =  30.77        (20 %)
     *      taxes       =  46.15
     *      inclusive   = 200.00
     * 
     * @return void
     */
    public function test_calculate_taxes_correctly()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->stock = 10;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        // Create Bag
        $bag = PriceBag::fromCart($cart);

        // Check
        $this->assertEquals(
            15384, 
            $bag->totalExclusive()->integer()
        );
        $this->assertCount(
            2, 
            $bag->productsTaxes()[0]['taxes']
        );
        $this->assertEquals(
            1538,
            $bag->productsTaxes()[0]['taxes'][0]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            3077,
            $bag->productsTaxes()[0]['taxes'][1]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            4615, 
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            20000, 
            $bag->totalInclusive()->integer()
        );
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
            $bag->totalInclusive()->integer()
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
            $bag->totalInclusive()->integer()
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
            $bag->totalInclusive()->integer()
        );
    }
    
    /**
     * Test if the PriceBag calculates taxes correctly when passed excluded.
     * 
     * Results
     *      exclusive   =  80.00 x 2
     *      no. taxes   = 2
     *      tax 1       =  16.00        (10 %)
     *      tax 2       =  32.00        (20 %)
     *      taxes       =  48.00
     *      inclusive   = 208.00
     * 
     * @return void
     */
    public function test_calculate_excluded_taxes_correctly()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        // Create Product
        $product = $this->getProduct(80);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            16000, 
            $bag->totalExclusive()->integer()
        );
        $this->assertCount(
            2, 
            $bag->productsTaxes()[0]['taxes']
        );
        $this->assertEquals(
            1600,
            $bag->productsTaxes()[0]['taxes'][0]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            3200,
            $bag->productsTaxes()[0]['taxes'][1]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            4800, 
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            20800, 
            $bag->totalInclusive()->integer()
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
            $bag->totalInclusive()->integer()
        );
    }

    /**
     * Test if Shipping Costs are calculated correctly.
     * @return void
     */
    public function test_calculate_shipping_costs()
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
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];
        $shippingMethod->taxes()->attach($tax1);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);
        $cart->setShippingMethod($shippingMethod);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertCount(
            2,
            $bag->productsTaxes()[0]['taxes']
        );
        $this->assertEquals(
            1538, 
            $bag->productsTaxes()[0]['taxes'][0]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            3077, 
            $bag->productsTaxes()[0]['taxes'][1]->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            909, 
            $bag->shippingTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            5524,
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            30000, 
            $bag->totalInclusive()->integer()
        );
    }

    /**
     * Test if forced Shipping Costs are calculated correctly.
     * @return void
     */
    public function test_calculate_forced_shipping_costs()
    {
        $tax1 = $this->getTax('Test 1', 10);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->taxes()->attach([$tax1->id]);
        $product->save();

        // Create Shipping Method
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->taxes()->attach([$tax1->id]);
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];
        $shippingMethod->taxes()->attach($tax1);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);
        $cart->setShippingMethod($shippingMethod);
        $cart->forceShippingPrice($shippingMethod->id, ['CHF' => 200, 'EUR' => 150], 'Enforced Price');

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            5152, 
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            40000, 
            $bag->totalInclusive()->integer()
        );
    }

    /**
     * Test if taxes are calculated correctly, when product is excluded.
     * @return void
     */
    public function test_calculate_taxes_correctly_when_excluded_with_shipping()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 10);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id]);
        $product->save();

        // Create Shipping Method
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];
        $shippingMethod->taxes()->attach($tax2);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 1);
        $cart->setShippingMethod($shippingMethod);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertCount(
            2, 
            $bag->totalTaxes()
        );
        $this->assertEquals(
            1000, 
            $bag->totalTaxes()[0]['vat']->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            909, 
            $bag->totalTaxes()[1]['vat']->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            1909, 
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            21000, 
            $bag->totalInclusive()->integer()
        );
    }

    /**
     * Test if taxes are calculated correctly, when product is excluded.
     * @return void
     */
    public function test_calculate_taxes_correctly_when_excluded_with_quantity()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id]);
        $product->save();

        // Create Shipping Method
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];
        $shippingMethod->taxes()->attach($tax2);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 3);
        $cart->setShippingMethod($shippingMethod);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertCount(
            2, 
            $bag->totalTaxes()
        );
        $this->assertEquals(
            3000, 
            $bag->totalTaxes()[0]['vat']->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            1667,
            $bag->totalTaxes()[1]['vat']->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            4667, 
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            43000, 
            $bag->totalInclusive()->integer()
        );
    }

    /**
     * Test if detailed taxes are calculated correctly on excluded prices.
     * @return void
     */
    public function test_calculate_detailed_taxes_on_excluded_with_shipping()
    {
        $tax1 = $this->getTax('Test 1', 10);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = false;
        $product->taxes()->attach([$tax1->id]);
        $product->save();

        // Create Shipping Method
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];
        $shippingMethod->taxes()->attach($tax1);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 1);
        $cart->setShippingMethod($shippingMethod);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertCount(
            2, 
            $bag->totalTaxes()
        );
        $this->assertEquals(
            1000, 
            $bag->totalTaxes()[0]['vat']->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            909, 
            $bag->totalTaxes()[1]['vat']->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            1909, 
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            21000, 
            $bag->totalInclusive()->integer()
        );
    }

    /**
     * Test if products weight is calculated correctly.
     * @return void
     */
    public function test_calculate_product_weight_total()
    {

        // Create Products
        $product1 = $this->getProduct(100);
        $product1->weight = 1000;
        $product1->save();

        $product2 = $this->getProduct(100);
        $product2->weight = 500;
        $product2->save();

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product1, 2);
        $cart->addProduct($product1, 1);
        $cart->addProduct($product2, 3);
        $cart->addProduct($product2, 1);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            5000, 
            $bag->productsWeight()
        );
    }

    /**
     * Test if products weight is calculated correctly.
     * @return void
     */
    public function test_calculate_product_weight_total_using_variants()
    {

        // Create Product
        $product = $this->getProduct(100);
        $product->weight = 1000;
        $product->save();

        // Create Variant
        $variant1 = Variant::create([
            'name'       => 'Variant with Weight',
            'product_id' => $product->id,
            'weight'     => 2000,
            'stock'      => 20,
        ]);
        $variant1->price = ['CHF' => 100, 'EUR' => 150];

        $variant2 = Variant::create([
            'name'       => 'Variant without Weight',
            'product_id' => $product->id,
            'stock'      => 20,
        ]);
        $variant2->price = ['CHF' => 100, 'EUR' => 150];

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2, $variant1);
        $cart->addProduct($product, 1, $variant2);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            5000, 
            $bag->productsWeight()
        );
    }

    /**
     * Test if shipping costs are calculated correctly using a shipping rate.
     * @return void
     */
    public function test_calculate_shipping_cost_using_rate()
    {
        $tax1 = $this->getTax('Test 1', 10);
        $tax2 = $this->getTax('Test 2', 20);

        // Create Product
        $product = $this->getProduct(100);
        $product->price_includes_tax = true;
        $product->weight = 1000;
        $product->taxes()->attach([$tax1->id, $tax2->id]);
        $product->save();

        // Create Shipping Method
        $shippingMethod = ShippingMethod::first();
        $shippingMethod->save();
        $shippingMethod->price = ['CHF' => 100, 'EUR' => 150];
        $shippingMethod->taxes()->attach($tax1);

        // Create Shipping Rate
        $rate = ShippingMethodRate::create([
            'from_weight'        => 2000,
            'shipping_method_id' => $shippingMethod->id,
        ]);
        $rate->price = ['CHF' => 200, 'EUR' => 250];

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2);
        $cart->setShippingMethod($shippingMethod);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertCount(
            2, 
            $bag->productsTaxes()[0]['taxes']
        );
        $this->assertEquals(
            4615, 
            $bag->productsTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            1818,
            $bag->shippingTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            6433, 
            $bag->totalTax()->getMinorAmount()->toInt()
        );
        $this->assertEquals(
            40000, 
            $bag->totalInclusive()->integer()
        );
    }

    /**
     * Test is variant costs are calculated correctly.
     * @return void
     */
    public function test_calculates_variant_cost()
    {

        // Create Product
        $product = Product::first();
        $product->stackable = true;
        $product->save();
        $product->price = ['CHF' => 20000, 'EUR' => 15000];

        // Create Variant
        $variant = Variant::create([
            'name'       => 'Variant',
            'product_id' => $product->id,
            'stock'      => 20,
        ]);
        $variant->price = ['CHF' => 10000, 'EUR' => 15000];
        $variant = Variant::find($variant->id);

        // Create Cart
        $cart = $this->getCart();
        $cart->addProduct($product, 2, $variant);
        $cart->addProduct($product, 1, $variant);

        // Create Bag
        $bag = PriceBag::fromCart($cart);
        $bag->applyDiscounts();

        // Check
        $this->assertEquals(
            300 * 100, 
            $bag->totalInclusive()->integer()
        );
    }

}
