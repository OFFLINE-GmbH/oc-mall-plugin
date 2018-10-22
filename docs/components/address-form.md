# AddressForm

The `AddressForm` component displays a address edit form.

## Properties

::: tip
All of these properties can be filled with URL parameters and should not be modified directly.
:::

### `address` (string)

A `HashId` of an Address model. If `new` is passed, a new Address model will be created. 

### `redirect` (string)

Whether to redirect the user to the `checkout` or `account` page.

### `set` (string)

Whether to set the address as `billing` or `shipping` address for the user's cart after saving.

## Example implementations


### Display the address form

```ini
title = "Address"
url = "/address/:address?/:redirect?/:set?"

[session]
security = "user"
redirect = "home"

[addressForm]
address = "{{ :address }}"
redirect = "{{ :redirect }}"
set = "{{ :set }}"
```