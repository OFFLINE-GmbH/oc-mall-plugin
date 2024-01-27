# Services

Services are additional offerings for a product 
(e. g. extended warranty, on-site installation).

You can attach any number of services to a product on the 
edit product form in the backend (cart section). 

If a product with attached services is added to the cart, a modal appears
 where service options can or must be selected.

Each service needs at least one option. Each option has a price.
A service can be specified to be required when a certain product 
is added to the cart.

By default, only one of the provided options can be selected.
By changing the [type of the `radio` inputs to `checkbox`](https://github.com/OFFLINE-GmbH/oc-mall-plugin/blob/317508f6bcbb7d280e96e379d1cec9b0636dc207/components/product/servicemodal.htm#L36), a user
will be able to select multiple options for a single service. 
