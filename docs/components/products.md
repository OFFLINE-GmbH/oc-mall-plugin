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
| `bestseller` | Best selling first    |
| `latest`     | Latest products first |
| `oldest`     | Oldest products first | 
| `price_low`  | Lowest price first    |
| `price_high` | Highest price first   |

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