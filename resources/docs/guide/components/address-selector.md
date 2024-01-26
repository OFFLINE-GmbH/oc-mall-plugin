# AddressSelector

The `AddressSelector` component displays an address that belongs to the user's cart. It gives  the user the 
ability to edit it or select a different address.

This component is part of the [Checkout component](./checkout.md) and is used to display the currently active billing and shipping 
Address on the checkout page. 
 
## Properties

### `type` (string)

Display the `billing` or `shipping` address of the user's cart. 

## Example implementations

::: tip 
This component is part of the [Checkout component](./checkout.md). You do not have to implement it separately.
::: 

### Display the address selector

```ini
[addressSelector shippingAddress]
type = "shipping"

[addressSelector billingAddress]
type = "billing"
==

{% component 'shippingAddress' %}
{% component 'billingAddress' %}
```