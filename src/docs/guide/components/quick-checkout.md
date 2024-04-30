# QuickCheckout

> [!DANGER] Work in Progress
> We are currently revising our documentation and the page you are currently looking at has not yet 
> been completed. Thus, the information here may therefore be incomplete or out of date.

The `QuickCheckout` component allows the creation of an order on a single page.
This component is meant as an alternative to the [Checkout](./checkout.md) component.

## Properties

### `step` (string)

The currently active checkout step. This parameter should be filled via URL parameter.

### `loginPage` (string)

The name of the cms page that hosts the `signUp` component. By default, a sign up form is
displayed by the `quickCheckout` component. For users with an existing account a "Log in"
link is available. This property determines what cms page will be used for that link.

## Example implementations

### Display the quick checkout page

```ini
title = "Checkout"
url = "/checkout/:step?"

[quickCheckout]
step = "{{ :step }}"
==
{% component 'quickCheckout' %}
```
