# Pricing information

By default the following models have pricing information associated with them:

* Product
* Variant
* ShippingMethod
* ShippingMethodRate
* PaymentMethod
* CustomField
* CustomFieldOption
* CustomerGroup


## Relations

Pricing information is stored using a central `Price` model.
Variants and Products return a special `ProductPrice` model that
extends the base `Price` model.

You can access the pricing information associated with a model by calling the `price()` relationship.

```php
$product->price();
> OFFLINE\Mall\Models\ProductPrice { ... }
```

Some models have additional price relationships for special fields like `availableBelowTotal` ( 
ShippingMethod) or `totalToReach` (Discounts). All of these relationships return the same `Price` models.
 
```php
$shippingMethod->availableBelowTotal();
> OFFLINE\Mall\Models\Price { ... }

$discount->totalToReach();
> OFFLINE\Mall\Models\Price { ... }
```

The relationship takes an optional `Currency` attribute. If no currency is specified the currently active one is used.
The currency can be supplied by ID, code or even as a Currency model instance. 

```php
$product->price('EUR');
> OFFLINE\Mall\Models\ProductPrice { ... } // Price in EUR

$product->price(2);
> OFFLINE\Mall\Models\ProductPrice { ... } // Price in EUR
```


::: tip
You can find all of the available price accessor methods by looking into the model's source code.  
:::

## Formats

You can transform the price of a model by calling special getters on the `price()` relation:

```php
// Get the Price model in the active currency.
$product->price();

> OFFLINE\Mall\Models\ProductPrice {
   id: 1,
   price: 89500,
   product_id: 1,
   variant_id: null,
   currency_id: 1,
   created_at: "2018-10-20 10:47:25",
   updated_at: "2018-10-20 10:47:25",
   currency: OFFLINE\Mall\Models\Currency { ... },
 }
 
// Get the Price in the active currency in different formats.
$product->price()->integer;
> 89500

$product->price()->float;
> 895.0

$product->price()->decimal;
> "895.00"

$product->price()->string;
> "$ 895.00"

(string) $product->price();
> "$ 895.00"

// Get the price in a different currency.
$product->price('EUR')->string;
> "795.00 €"

// Can also be done by id.
$product->price(2)->string;
> "795.00 €"

// Or by model.
$currency = \OFFLINE\Mall\Models\Currency::find(2);
$product->price($currency)->string;
> "795.00 €"

// Get an array of all currencies as formatted strings.
$model->price;
> October\Rain\Database\Collection {#3412
   all: [
     "USD" => "$ 895.00",
     "EUR" => "795 00€",
     "CHF" => "CHF 899.00",
   ],
 }
```