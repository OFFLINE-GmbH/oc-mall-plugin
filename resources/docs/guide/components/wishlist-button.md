# WishlistButton

The `wishlistButton` component displays a `Add to wishlist` button.

The button includes a simple popup where the user can choose a specific
wishlist or create a new one.
 
## Properties

You have to provide a product and variant ID of the product or variant
to be added to the wishlist.

The variant id can be `null`. 

## Styling

Take a look
at [the relevant SCSS files in our demo repository](https://github.com/OFFLINE-GmbH/oc-mall-theme/blob/master/resources/scss/mall/wishlists.scss)
to get an idea on how to style this component.

## Events

This component emits a `mall.wishlist.productAdded` JavaScript event when 
a product was added to a wishlist. You can subscribe to this event
and receive the added product's information.

```js
$(function () {
    $.subscribe('mall.wishlist.productAdded', function (data) {
        console.log('product added to wishlist', data)
    });
});
``` 

## Example implementations

### Display the wishlist button

If you place this button inside the [Product component](./product.md)
you can reference the `item` variable to access the currenlty viewed product/variant.

Checkout [the source of the demo website](https://github.com/OFFLINE-GmbH/oc-mall-theme/blob/master/partials/product/belowcartbutton.htm)
 to see an example implementation.

```twig
[wishlistButton]
==
{%  component 'wishlistButton'
    product=item.product_id
    variant=item.variant_id
%}
```