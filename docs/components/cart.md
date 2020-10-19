# Cart

The `Cart` component displays the currently logged in user's cart.

It can also be used to display a "items in cart" type element.

## Properties

### `showDiscountApplier` (bool)

Display the [`DiscountApplier`](./discount-applier.md) component with the cart. This allows the user to apply a 
discount code directly from the cart overview.


### `showTaxes` (bool)

Display a tax summary at the end of the cart. If the user has not yet specified a shipping address this summary may 
not reflect the effective tax total if there are taxes with country restrictions.

### `showShipping` (bool)

Defaults to `true`. Include the shipping method/cost in the cart. Set this to false
if you don't want to include any shipping information.

### `showProceedToCheckoutButton` (bool)

Defaults to `false`. Include a `proceed to checkout` button below the cart
table.


## Example implementations

### Display the number of products in the cart

```ini
[cart cartButton]
==
<a href="{{ 'cart' | page }}">
    Products in cart:
    <span class="js-count">
        {{ cartButton.cart.products.count }}
    </span>
</a>
```

The following JS snippet can be used to update the displayed count
automatically when a product is added or removed from the cart.

```js
$(function () {
    var $count = $('.js-count');
    $.subscribe('mall.cart.productAdded', function (e, data) {
        // You have access to different values here.
        // console.log(data.item); // All item data
        // console.log(data.quantity); // Added quantity
        // console.log(data.new_items_count); // New total items in cart
        // console.log(data.new_items_quantity); // New total quantity of items in cart
        $count.text(data.new_items_count);
        // /* or */ $count.text(data.new_items_quantity);
    });
    $.subscribe('mall.cart.productRemoved', function (e, data) {
        // You have access to different values here.
        // console.log(data.item); // All item data
        // console.log(data.quantity); // Removed quantity
        // console.log(data.new_items_count); // New total items in cart
        // console.log(data.new_items_quantity); // New total quantity of items in cart
        $count.text(data.new_items_count);
        // /* or */ $count.text(data.new_items_quantity);
    });
});
```

### Display the cart table without tax summary with a proceed to checkout button

```ini
[cart]
showTaxes = 0
showProceedToCheckoutButton = 1
```

### Display the cart table with tax summary but don't let the user apply a discount 

```ini
[cart]
showDiscountApplier = 0
```

### Display the price difference until the shipping becomes free

```php
title = "Cart"
url = "/cart"

[cart]
==
<?php
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\ShippingMethod;

function onInit() {
    // Set this to your free shipping method
    $freeShippingMethod = 2;
    
    // Get the user's cart total
    $cart = Cart::byUser(Auth::getUser());
    $total = $cart->totals->productPostTaxes();

    // Get the free shipping method and check the total
    // it needs to become available.
    $freeShipping = ShippingMethod::find($freeShippingMethod);
    $totalNeeded  = $freeShipping->availableAboveTotal()->integer;
    
    // costLeft ist the difference the customer needs to add to
    // her cart until the free shipping becomes available.
    $this['costLeft'] = $totalNeeded - $total;
}
==
{% component 'cart' %}

{% if costLeft > 0 %}
    <div class="free-shipping-notice">
        {{ costLeft | money }} until your shipping becomes free!
    </div>
{% endif %}
```