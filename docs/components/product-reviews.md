# ProductReviews

The `ProductReviews` component displays all Reviews for a Product or Variant.

## Properties

### `product` (int)

ID of the Product model.

### `variant` (int)

ID of the Variant model.

### `currentVariantReviewsOnly` (boolean)

If Reviews are enabled, display only Reviews for the currently viewed Variant.

By default Reviews from all Variants of the same Product are shown.

## Example implementations

::: tip 
This component is included by the [Product component](./product.md) by default. You do not have to implement it separately.
::: 

### Display all Reviews of a Product

```ini
[productReviews]
product = "1"
variant = "4"
currentVariantReviewsOnly = "0"
```
