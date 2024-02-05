# Product model

The `Product` model is the central entity of your shop installation.

::: tip
These are only examples of the most commonly used relationships.
Take a look at the [Product model's code](https://github.com/OFFLINE-GmbH/oc-mall-plugin/blob/develop/models/Product.php) to gain deeper insights.
:::

## Access pricing information

To access a product's pricing information see [accessing pricing information](./pricing-information.md).

## Property values

A product's property values are accessible via the `property_values` relationship.


```php
$model->property_values;
```   

::: warning CAUTION
If you are working with Variants make sure to call the `all_property_values` relation instead.
This includes all property values of the `Product` and the `Variant` combined.
:::

To get the value of a specific property the `getPropertyValue` method can be used.

```php
// Returns the value of Property ID 4
$model->getPropertyValue(4);
// Returns the value of the Property with the "size" slug
$model->getPropertyValueBySlug('size');
// Returns the value of the Property with the "Size" name
$model->getPropertyValueByName('size');
```

## Grouped values

You can access all property values grouped by their property value group by using the `grouped_properties` 
accessor:

```twig
{% for entry in product.grouped_properties %}
    <h2>{{ entry.group.name }}</h2>
    {% for value in entry.values %}
        <strong>{{ value.property.name }}</strong><br>
        {{ value.display_value | raw }} {{ value.property.unit }}
    {% endfor %}
{% endfor %}
```

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
