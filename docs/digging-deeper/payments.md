# Payments

## Payment gateways

`Payment gateways` are of technical nature and won't be visible to the user. You can
use the payment gateway settings page to configure your API keys and other settings for
each gateway.

Each gateway can be used by one or many payment methods.

You can add custom gateways by implementing and registering a
[Payment provider class](./../development/payment-providers.md).

 
## Payment methods 

`Payment methods` are presented to a customer during checkout. They abstract 
away the technical nature of a `Payment gateway`. You can define a 
name, a description and a logo for each payment method.

You can also specify special payment instructions (see below) or fees 
that will be added to the user's cart during checkout.
   
Payment methods use a single `Payment gateway` to process a payment.

### Fees    

You can define fixed and variable fees that will be added to the user's cart
during checkout.

If, for example, you want to forward your Stripe fees to the customer, you can
add `0.30 USD` as fixed fee and `2.9 %` as percentage fee.  

### Payment instructions

You can specify special payment instructions using Twig syntax in the `Instructions` field of 
any payment method.

By default, these instructions will be displayed when using the
[Payment method selector](../components/payment-method-selector.md) or 
[Orders list](../components/orders-list.md) components.

You can access a `order` variable (if available) to display additional information. During the checkout
process you have access to the `cart` model since no order was created yet.

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