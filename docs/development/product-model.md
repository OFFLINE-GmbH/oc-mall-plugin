# Product model

The `Product` model is the central entity of your shop installation.

::: tip
These are only examples of the most commonly used relationships.
Take a look at the [Product model's code](https://github.com/OFFLINE-GmbH/oc-mall-plugin/blob/develop/models/Product.php) to gain deeper insights.
:::

## Access pricing information

To access a product's pricing information see [accessing pricing information](./pricing-information.md).

## Custom fields

Custom fields can be used by the customer to add additional information 
to a product when it is added to the shopping cart.

This enables you to add options like "Personal engraving" or "Wrap item as gift" to your products.

To access these fields use the `custom_fields` relationship.

```php
$model->custom_fields;
```    

## Accessories

Products can be linked together via the product form in the October CMS backend.
This is useful to display a "You might also like" or a "Accessories to this product"
list. 

To retrieve linked products you can use the following relationships:

```php
// Get all linked products
$model->accessories;

// Get all products this model is an accessory of
$model->is_accessory_of;
``` 
