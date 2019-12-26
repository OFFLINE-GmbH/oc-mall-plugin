# Cart model

The `Cart` model represents a customer's shopping cart.

## Retrieve a Cart 

You can use the following method to retrieve a new or existing
cart for the current customer:

```php
$cart = Cart::byUser(Auth::getUser());
```

## Enforce shipping price

In case you want to dynamically force a shipping price
for a Cart for a certain shipping method, you can use the following method:

```php
// Shipping method 1 will always cost 400 €
$cart->forceShippingPrice(1, ['EUR' => 400], 'Optional, alternative name');
```   

::: warning
Make sure to specify all Currencies you have installed in your shop.
**Unspecified currencies will not be automatically calculated and result
in a shipping price of 0!**
:::

This allows you to programmatically set a dynamic shipping price
that overrides the default information for the selected
shipping method.

This feature comes in handy if you need to set a price depending
on any given value (like shipping distance where each km means additional shipping cost).

To forget a forced shipping price simply use the following method:

```php
$cart->forgetForcedShippingPrice();
```
