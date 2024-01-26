# MyAccount

The `MyAccount` component displays an overview of the user's account.

It pulls together the [OrdersList](./orders-list.md), [CustomerProfile](./customer-profile.md) and [AddressList](
./address-list.md) components. 

By changing the `page` property a different child component is displayed.
 
## Properties

### `page` (string)

The currently active page. This property should be populated via URL parameters. Possible values are `orders`, 
`profile` and `addresses`.

### `order` (string, optional)

The hashed ID of an order. This will automatically open the order details in the overview.


## Example implementations

### Display the my account page

```ini
title = "Account"
url = "/account/:page?/:order?"

[session]
security = "user"
redirect = "login"

[myAccount]
page = "{{ :page }}"
==
{% component 'myAccount' %}
```