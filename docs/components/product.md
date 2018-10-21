# Product

The `Product` component is used to display a single [`Product` or `Variant`](../digging-deeper/products.md).

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