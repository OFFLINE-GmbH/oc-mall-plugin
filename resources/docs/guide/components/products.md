# Products

The `Products` component displays a list of products. The list can be sorted, filtered and paginated.

## Properties

### `category` (mixed)

Select only items from this [Category](../digging-deeper/categories.md). Possible values are:

| Value | Description |
| ----- | ----------- |
| `null` | Don't filter by category (display everything) |
| `8` | Only show products from the category with the ID `8` (use any integer) |
| `:slug` | Use the page's `:slug` URL parameter to find the category to filter by |

### `filterComponent` (string)

Alias of the [ProductsFilter](./products-filter.md) component that is used to filter
this `Products` component. Defaults to `productsFilter`

### `filter` (string)

Use this property to force a filter for this component instance.

The expected value is a encoded query string. You can tweak your filter
using the [ProductsFilter](./products-filter.md) component and simply copy and paste
the query string:

https://mall.offline.swiss/en/category/bikes[?on_sale=true&material=aluminium.carbon&color=dark-grey](https://mall.offline.swiss/en/category/bikes?on_sale=true&material=aluminium.carbon&color=dark-grey)

```
filter = "on_sale=true&material=aluminium.carbon&color=dark-grey"
```

### `includeChildren` (bool)

Include all products or variants from child categories as well.

### `setPageTitle` (bool)

If a category is defined, overwrite the page's title with the category's name. Also set any SEO information stored 
with the category. 

### `includeVariants` (bool)

Set this to `true` to list all [Variants](../digging-deeper/products.md) as single items.
Set this to `false` to list only [Products](../digging-deeper/products.md).


### `perPage` (int)

How many items to show per page.

### `paginate` (bool)

Display more than one page.

### `sort` (string)

The sort mode applied to the result set. Possible values are:

| Value        | Description           |
| ------------ | --------------------- |
| `manual`     | Order defined via Backend   |
| `bestseller` | Best selling first    |
| `latest`     | Latest products first |
| `oldest`     | Oldest products first | 
| `price_low`  | Lowest price first    |
| `price_high` | Highest price first   |
| `random`     | Sort randomly         |
| `name`       | Sort by name          |

## Example implementations

### Display all items of a category

```ini
[products]
category = ":slug"
setPageTitle = 1
includeVariants = 1
includeChildren = 1
perPage = 9
```

### Display the four best selling products

```ini
[products]
perPage = 4
paginate = 0
includeVariants = 0
sort = "bestseller"
```

### Display the four latest items

```ini
[products]
perPage = 4
paginate = 0
includeVariants = 1
sort = "latest"
```

### Display eight random products that are currently on sale

```ini
[products]
perPage = 8
paginate = 0
includeVariants = 1
sort = "random"
filter = "on_sale=true"
```

### Display four random items from the same category of the currently viewed product

```ini
title = "Product"
url = "/product/:slug/:variant?"
layout = "default"
is_hidden = 0

[product]
product = ":slug"
variant = ":slug"

[products relatedProducts]
setPageTitle = 0
includeVariants = 1
includeChildren = 0
perPage = 4
paginate = 0
sort = "random"
==
use OFFLINE\Mall\Models\Category;

function onStart() {
    // Fetch the category from the product component.
    $productComponent = $this->findComponentByName('product');
    $item = optional($productComponent)->item;
    if (!$item) {
        return;
    }
    $category = optional($item->categories)->first();
    if (!$category) {
        return;
    }
    // If a category is available, use it for the products component.
    $this->findComponentByName('relatedProducts')->category = $category;
}
==
{% component 'product' %}

<h2>Other products from this category</h2>
{% component 'relatedProducts' %}
```

### Add a add to cart button on the category page

Add the following `a` tag to your item partial `/plugins/offline/components/products/item.htm`

```html
<div class="mall-products-item">

    <!-- Add this -->
    <a class="mall-products-item__cart-button"
       href="javascript:;"
       data-request="{{ __SELF__ }}::onAddToCart"
       data-request-success="data.added && $.publish('mall.cart.productAdded', data)" 
       data-request-data="product: '{{ item.productHashId }}', variant: '{{ item.variantHashId }}'"
       data-request-flash
    >
        Add to cart
    </a>
    <!-- /Add this -->
    
    <a href="{{ productPage|page({slug: item.slug, variant: item.variantId}) }}">
        <!-- Thumb, Title, etc. -->
    </a>
</div>
```