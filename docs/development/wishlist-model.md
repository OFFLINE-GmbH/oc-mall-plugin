# Wishlist model

The `Wishlist` model represents a customer's wishlist.

## PDF download

To enable PDF downloads of wishlists, simply create a partial in 
your theme directory at `partials/mallPDF/wishlist/default.htm`.

The `Wishlists` component will now display a download button by default.

::: v-pre
You have access to all Wishlist data inside the partial
using the `{{ wishlist }}` variable.
:::



You can programmatically generate a `Barryvdh\DomPDF\PDF` instance by
calling the `getPDF` method:

```php
$pdf = $wishlist->getPDF();

$pdf->save('/path/wishlist.pdf'); // Save to disk
$pdf->stream();                   // Return download response
$pdf->output();                   // Return string representation
```

Take a look at the demo theme for an example implementation:
[https://github.com/OFFLINE-GmbH/oc-mall-theme/tree/master/partials/mallPDF/wishlist](https://github.com/OFFLINE-GmbH/oc-mall-theme/tree/master/partials/mallPDF/wishlist)
