<script setup>
import BadgeStd from '../../.vitepress/components/BadgeStd.vue'
</script>

# Price Bag
<BadgeStd label="New in 3.2.0" color="warning" />

The **PriceBag** construct is the new system for calculating and evaluating the applied (customer) 
price structure, and thus replaces the obsolete `TotalsCalculator` classes. This was an important
step, since it was not only difficult to maintain and extend, but also because it already performed 
incorrect calculations (at least [when a discount](https://github.com/OFFLINE-GmbH/oc-mall-plugin/issues/423) 
was used).

**PriceBag** differs by this points:

1. It works almost completely detached, although it allows references to the "original" models and 
provides additional parser methods for the Carts, Orders and Wishlists.
2. It collects, handles and calculates the passed prices individually and on per-stack basis instead
of creating confusing subtotals.
3. It supports using only one single VAT as factor value, however multiple taxes can still be applied 
using either a factor or a fixed amount.
4. It uses [whitecube/php-prices](https://github.com/whitecube/php-prices) and [brick/money](https://github.com/brick/money) 
for calculating and formatting the desired prices and amounts.

::: info
As PriceBag was developed separately from Mall, not all of the possibilities offered by PriceBag have 
been integrated into Mall at this time. For example, Mall does currently not support "cash" discounts 
or skonto / conto amounts.
:::


## How it works

**PriceBag** collects the prices in 5 different stacks:

1. `products`: The main products of the bag. Each record in this stack must have at least a price 
(exclusive or inclusive VAT and taxes) and a quantity and can additionally be assigned with a VAT 
(factor only), additional taxes (factor and fixed amounts). discounts (factor and fixed amounts), as 
well as an additional weight value.

2. `services`: Additional services of the bag. Similar to the products stack, but is not subject to 
bag-global discounts, as described below, and cannot have any weight assigned.

3. `shipping`: The assigned shipping methods, including the raw prices without discount. Shipping 
methods can also contain weight-limitations, package-amount calculations and product associations.

4. `payment`: The assigned payment methods. In the case of multiple methods, each one must indicate 
the amount which has or should be paid using it. Moreover, each method can add additional fees, or 
discounts.

5. `discounts`: The bag-global discounts, which can be calculated on the net-total of all products 
or services, on the payment fees, the shipping costs or the gross amount of the cart.

and calculates them using this order:

```
+ Products
    Product
        (net-value * quantity)
        - discounts
        + vat
        + taxes
+ Services
    Service
        (net-value * quantity)
        - discounts
        + vat
        + taxes
+ Shipping Costs
    Method
        (net-value * packages)
        + vat
        + taxes
- Shipping Discounts
+ Payment Fees
- Payment Discounts
- Gross Discounts
===========
Gross Total
```

## Creating a new instance

A new instance of the PriceBag class can be created at any time. Either directly using the PriceBag 
constructor or with the help of an existing bag / model, such as Cart, Order or Wishlist. Using the 
first method allows you to specify the used currency (either by using the `Currency` model or by 
using the currency code directly).

```php
new PriceBag(null|string|Currency $currency = null): PriceBag;
```

```php
PriceBag::fromCart(Cart $cart): PriceBag;
```

```php
PriceBag::fromOrder(Order $order): PriceBag;
```

```php 
PriceBag::fromWishlist(Wishlist $wishlist): PriceBag;
```

## Add Products / Services
Once a PriceBag has been created, the individual products and services can be added. This requires a 
product (either a string or the eloquent model), the price (`amount`) itself as well as the desired 
quantity (`units`). Please note that the submitted price ALWAYS applies to one single unit and not 
the total number of units specified. By default, the price is also stored as a net value; you can 
change this using the 4th parameter.

Using the function `addProduct` or `addService` returns an instance of the `PriceRecord` class. You 
can use this to set the VAT, the additional taxes and the product-specific discounts, as shown in 
the example code below.

```php
// `addProduct` and `addService` share the same syntax
$record = (new  PriceBag('EUR'))->addProduct(
    string|Model            $product,
    int|float|string        $amount,
    int                     $units = 1,
    bool                    $isInclusive = false
);

// Set main vat
$record->setVat(
    int|float               $factor
);

// Set additional taxes
$record->addTax(
    int|float|string|Price  $factorOrAmount, 
    bool                    $isFactor = true
);

// Set discounts
$record->addDiscount(
    int|float|string|Price  $factorOrAmount, 
    bool                    $isFactor = true, 
    bool                    $perUnit = false
);
```

## Add Shipping Methods



```php
```

## Add Payment Methods

## Add Bag Discounts

## Calculate Totals

The total values are calculated using the different methods of the `PriceBag` instance, which in 
turn calls the corresponding methods of the individual records and either adds them together or 
returns them as a collection array.

### Product Totals

The following methods do only calculate the respective totals of the `products` stack!

#### Exclusive

Calculates and returns the exclusive price value (prices without discount, vat or any other tax
applied) of the whole `products` stack.

```php
$bag->productsExclusive(): PriceValue;
```

#### Discount

Calculates and returns the sum of all applied discounts for all `products` combined.

```php
$bag->productsDiscount(): AbstractMoney;
```

#### VAT

Calculcates and returns VAT only, based on the original net-price minus discounts for all `products`
combined. Passing `true` groups the sum of all VATs according to the applied factor.

```php
$bag->productsVat(bool $grouped = false): AbstractMoney|AbstractMoney[];
```

#### Taxes

Calculcates and returns all taxes including VAT, based on the original net-price minus discounts for 
all `products` combined. Passing `true` group the sum of all VATs according to the applied factor, 
adding an additional `taxes` key providing the sum of all other applied taxes.

```php
$bag->productsTax(bool $grouped = false): AbstractMoney|AbstractMoney[];
```



    public function productsTax(): null|AbstractMoney
    {

    }
    public function productsInclusive(): PriceValue
    {

    }
    public function productsWeight(): PriceValue
    {

    }

    /**
     * Return exclusive price value for all services (prices without discount, vat or taxes applied).
     * @return PriceValue
     */
    public function serviceExclusive(): PriceValue
    {

    }

    /**
     * Return sum of all discounts for all services combined.
     * @return null|AbstractMoney
     */
    public function serviceDiscount(): null|AbstractMoney
    {

    }

    /**
     * Return only vat based on the original net-price minus discount for all products combined.
     * @return null|AbstractMoney
     */
    public function serviceVat(): null|AbstractMoney
    {

    }
    public function serviceTax(): null|AbstractMoney
    {

    }
    public function serviceInclusive(): PriceValue
    {

    }
    
    /**
     * Return all applied Payment Fees.
     * @return AbstractMoney[]
     */
    public function paymentFees(): array
    {

    }
    
    /**
     * Return all applied Payment Discounts.
     * @return AbstractMoney[]
     */
    public function paymentDiscounts(): null|AbstractMoney
    {

    }

    /**
     * Return exclusive price value for all shipping methods (prices without discount, vat or taxes applied).
     * @return PriceValue
     */
    public function shippingExclusive(): PriceValue
    {

    }
    public function shippingVat(): null|AbstractMoney
    {

    }
    public function shippingTax(): null|AbstractMoney
    {

    }
    public function shippingInclusive(): PriceValue
    {

    }
    public function shippingDiscount(): null|AbstractMoney
    {

    }

    /**
     * Return total price of all stacks (prices without discount, vat or taxes applied).
     * @return PriceValue
     */
    public function totalExclusive(): PriceValue
    {

    }
    public function totalVat(): null|AbstractMoney
    {

    }
    public function totalTax(): null|AbstractMoney
    {

    }
    public function totalDiscount(): null|AbstractMoney
    {

    }
    public function totalInclusive(): PriceValue
    {

    }