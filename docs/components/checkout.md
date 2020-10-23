# Checkout

The `Checkout` component orchestrates the complete checkout process.
An alternative to this component is the [QuickCheckout](./quick-checkout.md) component.

By changing the `step` property, a different checkout step will be displayed. The component takes 
care of all redirects and component initialisations.

## Properties

### `step` (string)

The currently active checkout step. This parameter should be filled via URL parameter.


## Example implementations

### Display the checkout page if the user is logged in

```ini
title = "Checkout"
url = "/checkout/:step?"

[signUp]

[checkout]
step = "{{ :step }}"
==
{% if not user %}
    {% component 'signUp' %}
{% else %}
    {% component 'checkout' %}
{% endif %}
```