# Product

The `Product` component displays a single [`Product` or `Variant`](../digging-deeper/products.md).

## Properties

### `product` (mixed)

Display a specific `Product`. Possible values are:

| Value | Description |
| ----- | ----------- |
| `:slug` | Use the page's `:slug` URL parameter to find the Product to display |
| `8` | Display the Product with ID `8` (use any integer) |

### `variant` (mixed)

Display a specific `Variant`. Possible values are:

| Value | Description |
| ----- | ----------- |
| `:slug` | Use the page's `:slug` URL parameter to find the Variant to display |
| `8` | Display the Variant with ID `8` (use any integer) |

### `redirectOnPropertyChange` (boolean)

Redirect the user to the new Variant/Product detail page if a property was changed.

Default behaviour is to only reload the "add to cart" partial where new pricing
information and stock values are displayed. The title, description and product images
remain unchanged. Set this property to true if you need the whole page to update.

### `currentVariantReviewsOnly` (boolean)

If Reviews are enabled, display only Reviews for the currently viewed Variant.

By default Reviews from all Variants of the same Product are shown.

## Example implementations

### Display the Variant defined in the URL

```ini
[product]
product = ":slug"
variant = ":slug"
```

### Display Variant ID 2 of Product ID 1 

```ini
[product]
product = "1"
variant = "2"
```

### Always update the whole page if a product property has changed

```ini
[product]
product = ":slug"
variant = ":slug"
redirectOnPropertyChange = 1
```