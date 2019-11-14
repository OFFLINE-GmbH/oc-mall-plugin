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

Any payment instructions are by default rendered in the confirmation mails
via the `payment_state` mail partial.

See [Payment methods](./../digging-deeper/payments.md#specifying-payment-instructions)
 for further information on how to use payment instructions.

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

## Download PDF invoice

To download a PDF invoice for an Order, use the `getPDFInvoice` method to 
get a `Barryvdh\DomPDF\PDF` instance.

Beware that only orders that have a [payment method with a
valid `pdf_partial` assigned](./../digging-deeper/payments.md#pdf-invoices) will support this feature.

```php
$pdf = $order->getPDFInvoice();

$pdf->save('/path/invoice.pdf'); // Save to disk
$pdf->stream();                  // Return download response
$pdf->output();                  // Return string representation
```