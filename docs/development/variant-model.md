# Variant model

A `Variant` model always belongs to a parent [Product model](./product-model.md).

Variant models by default inherit most of the data from their parent Product model.
That's why you can use almost all of the information from
the [Product model documentation](./product-model.md) and apply it to Variants as well.
 
This page only documents the differences between Product and Variant models you need to be aware of.  

## Access pricing information

To access a product's pricing information see [accessing pricing information](./pricing-information.md).

## Property values

A variants's property values are accessible via the `property_values` relationship.


```php
$variant->property_values;
```   

The call above only returns the property values that are directly linked to this Variant model.
If you want to get a collection of *all* values, including the ones attached to the 
parent Product model, use `all_property_values` instead.


```php
// Include values attached to the parent Product
$variant->all_property_values;
```