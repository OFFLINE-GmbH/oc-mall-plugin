# ProductsFilter

The `ProductsFilter` displays filters for a category. It's selection will be applied to a result set displayed by the
 [Products component](./products.md).
 
::: warning
You don't have to specify the `includeChildren`, `includeVariants` and `category` properties.
The values from the corresponding [Products component](./products.md) will be used automatically. 

If you for whatever reason want to specify them anyway, make sure to use the same values as you 
have used in the Products component.
:::

## Properties

### `category` (mixed)

Select only items from this [Category](../digging-deeper/categories.md). Possible values are:

| Value | Description |
| ----- | ----------- |
| `null` | Don't filter by category (display everything) |
| `8` | Only show products from the category with the ID `8` (use any integer) |
| `:slug` | Use the page's `:slug` URL parameter to find the category to filter by |

If not specified the same value as specified on the [Products component](./products.md) will be used.

### `includeChildren` (bool)

Include all products or variants from child categories as well.

If not specified the same value as specified on the [Products component](./products.md) will be used.

### `includeVariants` (bool)

Set this to `true` to filter by all Variant properties, not only Product properties.

If not specified the same value as specified on the [Products component](./products.md) will be used.

### `showPriceFilter` (bool)

Display a price range filter.

### `showBrandFilter` (bool)

Display a brands dropdown.

### `showOnSaleFilter` (bool)

Display a checkbox to only display products that are on sale.

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
showPriceFilter = 1
showBrandFilter = 1
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