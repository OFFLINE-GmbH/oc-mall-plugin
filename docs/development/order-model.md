# Order model

The `Order` model represents a customer's completed order.

A `Order` model is created as soon as the customer hits the checkout button.
The order is persisted even if the payment fails. This enables a 
customer to retry the payment at any time.

## Payment ID for offline payments

If you offer offline payments you might want to have a unique payment ID to 
identify any incoming payments. This can be done using the `payment_hash` attribute
on the Order model.

```php
echo $order->id;
=> 17
echo $order->payment_hash;
=> "ng4gNkm5"
```

The `payment_hash` is presented to the shop admin on the orders backend page.

### Display offline payment info in checkout mails

You could add the following code to the `offline.mall::mail.checkout.succeeded` mail template:

```twig
{% if not order.payment_state == 'OFFLINE\\Mall\\Classes\\PaymentState\\PaidState' %}
    {% partial 'panel' body %}
        The payment for this order is still pending.
        
        Please send **{{ order.totalPostTaxes.string }}** to the following bank account:
        
        <Your bank connection>
        Payment reason: {{ order.payment_hash }}
    {% endpartial %}
{% endif %}
```   

## Access pricing information

You can access the following price relationships on the `Order` model. Refer
to the [pricing information](./pricing-information.md) page for more
information on how to use these.

| Relation                 | Description                        |             
| ------------------------ | ---------------------------------- |             
| totalPreTaxes            | Grand total before taxes           |             
| totalTaxes               | Grand total taxes                  |             
| totalPostTaxes           | Grand total after taxes            |             
| totalProductPreTaxes     | Product total before taxes         |             
| totalProductTaxes        | Product total taxes                |             
| totalProductPostTaxes    | Product total after taxes          |             
| totalShippingPreTaxes    | Shipping cost before taxes         |             
| totalShippingTaxes       | Shipping cost taxes                |             
| totalShippingPostTaxes   | Shipping cost after taxes          |             
| totalPaymentPreTaxes     | Payment provider cost before taxes |             
| totalPaymentTaxes        | Payment provider cost taxes        |             
| totalPaymentPostTaxes    | Payment provider cost after taxes  |             