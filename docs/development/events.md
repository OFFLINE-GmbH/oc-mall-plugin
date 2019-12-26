# Events

The oc-mall plugin emits the following events:

## Order

### `mall.order.beforeCreate`

An order is about to be created. This event receives the `$cart` model that is about to be converted into an `Order` model.

### `mall.order.state.changed`

An order's `OrderState` has changed. This event receives the modified `$order` as a single argument.

### `mall.order.tracking.changed`

An order's tracking information has changed. This event receives the modified `$order` as a single argument.

### `mall.order.payment_state.changed`

An order's payment state has changed. This event receives the modified `$order` as a single argument.

### `mall.order.shipped`

An order has been marked as shipped. This event receives the shipped `$order` as a single argument.

## Customer

### `mall.customer.beforeSignup`

This event is emitted before a new customer account is created. This event receives the `SignupHandler` implementation 
and all sign up form data as arguments.

### `mall.customer.afterSignup`

This event is emitted after a new customer account was created. This event receives the `SignupHandler` implementation 
and the created `User` model as arguments. 

### `mall.customer.beforeAuthenticate`

This event is emitted when a existing customer tries to sign in. This event receives the `SignupHandler` 
implementation and the provided credentials as arguments. 

## Cart

### `mall.cart.product.added`

This event is emitted when a product has been added to the cart. It receives the following arguments:

* `CartProduct` model that was updated in the cart 

### `mall.cart.product.removed`

This event is emitted when a product has been removed from the cart. It receives the following arguments:

* `CartProduct` model that was removed from the cart 

### `mall.cart.product.updated`

This event is emitted when a cart product was updated. It receives the following arguments:

* `CartProduct` model that was updated in the cart 

### `mall.cart.product.quantityChanged`

This event is emitted when the quantity of a cart product has changed. It receives the following arguments:

* `CartProduct` model that was updated in the cart 
* `oldQuantity` the old quantity value 
* `newQuantity` the new quantity value 

## Checkout

### `mall.checkout.succeeded`

This event is emitted when a checkout has been completed successfully. It receives a `PaymentResult` as a single 
argument.

### `mall.checkout.failed`

This event is emitted when there was a problem during the checkout process. It receives a `PaymentResult` as a single 
argument.

## Review

### `mall.review.created`

This event is emitted when a review was created. It receives the created `Review` model as a single 
argument.

### `mall.review.updated`

This event is emitted when a review was updated. It receives the updated `Review` model as a single 
argument.

## Product

### `mall.product.file_grant.created`

This event is emitted when a product file grant was created. It receives the created `ProductFileGrant` model as 
well as the related `Product` model.

For a usage example see
[Virtual Products -> Generate user specific product files](../digging-deeper/virtual-products.md#generate-user-specific-product-files)
