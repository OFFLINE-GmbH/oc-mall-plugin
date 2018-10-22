# Cart

The `Cart` component displays the currently logged in user's cart.

## Properties

### `showDiscountApplier` (bool)

Display the [`DiscountApplier`](./discount-applier.md) component with the cart. This allows the user to apply a 
discount code directly from the cart overview.


### `showTaxes` (bool)

Display a tax summary at the end of the cart. If the user has not yet specified a shipping address this summary may 
not reflect the effective tax total if there are taxes with country restrictions.


## Example implementations

### Display the cart without tax summary

```ini
[cart]
showTaxes = 0
```

### Display the cart with tax summary but don't let the user apply a discount 

```ini
[cart]
showDiscountApplier = 0
```