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
