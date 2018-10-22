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
    var baseCount = '{{ cartButton.cart.products.count }}';
    var $count = $('.js-count');
    $.subscribe('mall.cart.productAdded', function () {
        $count.text(++ baseCount);
    });
    $.subscribe('mall.cart.productRemoved', function () {
        baseCount --;
        if (baseCount < 0) baseCount = 0;
        $count.text(baseCount);
    });
});
```

### Display the cart table without tax summary

```ini
[cart]
showTaxes = 0
```

### Display the cart table with tax summary but don't let the user apply a discount 

```ini
[cart]
showDiscountApplier = 0
```