# Theme setup

## Layout

The [`mallDependencies` component](../components/mall-dependencies.md) includes all required frontend assets.

The component should be placed on each layout that provides shop functionality.  

```ini
description = "Default Layout"

[mallDependencies]
==
<!DOCTYPE html>
...
```

## Pages

In this section you can find a minimal demo implementation of each CMS page `oc-mall` needs.

You are free to change them as you wish. Just make sure to keep the required `url` parameters.

::: tip
To get started quickly simply copy and paste the markup to the respective cms page file.
:::

### product.htm

The product page displays a single product using the [Product component](../components/product.md).


```twig
title = "Product"
url = "/product/:slug/:variant?"
layout = "default"

[product]
product = ":slug"
variant = ":slug"
==
{% component 'product' %}
``` 

### category.htm

The category page displays all products in a category using the [Products component](../components/products.md). The 
products can be filtered using the [ProductsFilter component](../components/products-filter.md).

```twig
title = "Category"
url = "/category/:slug*"
layout = "default"
is_hidden = 0

[products]
category = ":slug"
setPageTitle = 1
includeVariants = 1
includeChildren = 1
perPage = 9

[productsFilter]
category = ":slug"
showPriceFilter = 1
==
<div class="container">
    <div class="row">
        <div class="col-12 col-md-4">
            {% component 'productsFilter' %}
        </div>
        <div class="col-12 col-md-8">
            <h2>{{ products.category.name }}</h2>
            {% component 'products' %}
        </div>
    </div>
</div>
``` 

### cart.htm

The cart displays the cart to the user using the [Cart component](../components/cart.md). 

```twig
title = "Cart"
url = "/cart"
layout = "default"
is_hidden = 0

[cart]
showTaxes = 0
==
{% component 'cart' %}

{% if cart.products.count > 0 %}
    <a href="{{ 'checkout' | page }}" class="btn">
        Begin checkout
    </a>
{% endif %}
``` 

### checkout.htm

The checkout page hosts the complete checkout process. 

```twig
title = "Checkout"
url = "/checkout/:step?"
layout = "default"
is_hidden = 0

[signUp]
[checkout]
step = "{{ :step }}"
==
{% if not user %}
    {% component 'signUp' %}
{% else %}
    {% component 'checkout' %}
{% endif %}
``` 

### myaccount.htm

The my account page displays an account overview to the user using the
[myAccount component](../components/my-account.md). 

```twig
title = "Account"
url = "/account/:page?"
layout = "default"
is_hidden = 0

[session]
security = "user"
redirect = "login"

[myAccount]
page = "{{ :page }}"
==
{% component 'myAccount' %}
``` 

### address.htm

The address page displays an edit form for a user's address using the
[AddressForm component](../components/address-form.md). 

```twig
title = "Checkout"
url = "/checkout/:step?"
layout = "default"
is_hidden = 0

[signUp]
[checkout]
step = "{{ :step }}"
==
{% if not user %}
    {% component 'signUp' %}
{% else %}
    {% component 'checkout' %}
{% endif %}
``` 

### login.htm

The login form displays a signup form for unregistered users using the
[signUp component](../components/sign-up.md). 

```twig
title = "Login"
url = "/login"
layout = "default"
is_hidden = 0

[signUp]
redirect = "/account"
==
{% component 'signUp' %}
``` 