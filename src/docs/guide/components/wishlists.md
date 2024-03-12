# Wishlists

> [!DANGER] Work in Progress
> We are currently revising our documentation and the page you are currently looking at has not yet 
> been completed. Thus, the information here may therefore be incomplete or out of date.

The `wishlists` component displays all wishlists of the currently
logged in customer.
 
## Properties

### `showShipping` (bool)

Show a shipping method selector along with the Wishlist. Shipping cost will be included in the totals.


## Styling

Take a look
at [the relevant SCSS files in our demo repository](https://github.com/OFFLINE-GmbH/oc-mall-theme/blob/master/resources/scss/mall/wishlists.scss)
to get an idea on how to style this component.

## Events

This component emits a `mall.wishlist.productRemoved` JavaScript event when 
a product was removed from a wishlist. You can subscribe to this event
and receive the added product's information.

```js
$(function () {
    $.subscribe('mall.wishlist.productRemoved', function (data) {
        console.log('product removed from wishlist', data)
    });
});
``` 


## Example implementations

### Display the wishlists manager

```twig
[wishlists]
==
{% component 'wishlists' %}
```