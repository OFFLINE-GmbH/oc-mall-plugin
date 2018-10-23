# Theme setup

Once the plugin is [installed and configured](./installation.md), follow the steps below to set up your theme.

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

## Static pages menu

If you are using the `RainLab.Pages` plugin, you can add the `All mall shop categories` entry to your navigation. 

This will render a tree of all available categories in your theme. 

## Take a look around

At this point your shop is configured and set up correctly. 

::: tip
Run `php artisan mall:check` again to make sure there are no problems left.
:::

::: tip
In case you missed it: You can seed the shop with demo data.
Visit the [Installation Page](./installation.md) to find out more.
::: 

If you don't have a navigation in your theme yet, simply visit some of
these URLs to get a first impression of your new online store.


::: tip INFO
The following URLs only work if you copied the default URL structure.
Adapt the links to your custom URLs if you made any changes.
::: 

* http://example.test/category/bikes
* http://example.test/product/cruiser-1500
* http://example.test/login
* http://example.test/cart

