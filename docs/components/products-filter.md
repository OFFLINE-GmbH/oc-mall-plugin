# ProductsFilter

The `ProductsFilter` displays filters for a category. It's selection will be applied to a result set displayed by the
 [Products component](./products.md).
 
::: warning
When implementing this component it is important to specify the same values for the `includeChildren`, 
`includeVariants` and `category` properties as you defined for the corresponding [Products component](./products.md). 
:::

## Properties

### `category` (mixed)

Select only items from this [Category](../digging-deeper/categories.md). Possible values are:

| Value | Description |
| ----- | ----------- |
| `null` | Don't filter by category (display everything) |
| `8` | Only show products from the category with the ID `8` (use any integer) |
| `:slug` | Use the page's `:slug` URL parameter to find the category to filter by |

### `includeChildren` (bool)

Include all products or variants from child categories as well.

### `includeVariants` (bool)

Set this to `true` to filter by all Variant properties, not only Product properties.


### `showPriceFilter` (bool)

Display a price range filter.

### `showBrandFilter` (bool)

Display a brands dropdown.

### `includeSliderAssets` (bool)

Enable this property to automatically include [noUiSlider](https://github.com/leongersen/noUiSlider) assets via CDN.


## Example implementations

### Display a `ProductsFilter` along a `Products` component

```ini
[products]
category = ":slug"
setPageTitle = 1
includeVariants = 1
includeChildren = 1
perPage = 9

[productsFilter]
category = ":slug"
showPriceFilter = 1
showBrandFilter = 1
includeChildren = 1
includeVariants = 1
includeSliderAssets = 1
==
<div class="container">
    <div class="row">
        <div class="col-12 col-md-4">
            {% component 'productsFilter' %}  
        </div>
        <div class="col-12 col-md-8">
            <h2>{{ products.category.name }}</h2>
    
            {% component 'products' %}
        </div>
    </div>
</div>
```