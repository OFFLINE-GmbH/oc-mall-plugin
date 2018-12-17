# Payment methods

## Specifying payment instructions

You can specify special payment instructions using Twig syntax in the `Instructions` field of 
any payment method.

By default, these instructions will be displayed when using the
[Payment method selector](../components/payment-method-selector.md) or 
[Orders list](../components/orders-list.md) components.

You can access a `order` variable (if available) to display additional information.

Sample instructions for a "Payment in advance" method could be:

```twig
{# 
    Check if the order has been created yet.
    During checkout this might not be the case
#}
{% if order %}
    Send your payment to:
    *Bank information*
    
    Payment id: {{ order.payment_hash }}
{% endif %}
```