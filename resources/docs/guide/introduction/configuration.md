---
title: Configuration
editLink: true
---

# Configuration

Once the plugin is [installed](/guide/introduction/installation.html#using-marketplace), and the 
first [data is seeded](/guide/introduction/installation.html#seed-initial-data), you should 
configure your new Shop as described in the following steps. 

## Plugin: RainLab.Location

Visit your OctoberCMS backend and go to `Settings` -> `Location` -> `Country & States`. Make sure, 
that only the countries you are shipping to are enabled. Disable every other country in this list.

## Plugin: Mall Configuration

We highly recommend declaring and checking the following settings and settings pages.

1. `Mall: General` -> `General settings` (email address, CMS pages)
2. `Mall: General` -> `Currencies` -> [Read more here](/guide/usage/currencies.html)
3. `Mall: General` -> `Taxes` -> [Read more here](/guide/usage/taxes.html)
4. `Mall: Orders` -> `Payment methods` -> [Read more here](/guide/usage/payment-methods.html)
5. `Mall: Orders` -> `Shipping methods` -> [Read more here](/guide/usage/shipping-methods.html)

## Theme: Layout

Your OctoberCMS theme must provide some dependencies, required by the JavaScript environment of the 
**Mall** plugin. The following changes must be implemented in the layout, which is used on the 
individual store pages (as described in the section below).

If you're new to Octobers component system, you should definitively take a look at the 
[official documentation page](https://docs.octobercms.com/3.x/cms/themes/components.html#introduction).
You can also take a look at [the default layout](https://github.com/OFFLINE-GmbH/oc-mall-theme/blob/master/layouts/default.htm#L7)
of our demonstration theme for some guidance.

### Components

Mall requires at east 2 components

1. The `[mallDependencies]` component includes the most-basic required front-end assets. You need to 
add the component within the `ini` configuration section of your theme and include it with the 
familiar TWIG-Syntax inside the `<head>` area of your layout.
2. The `[session]` component, provided by the RainLab.Users plugin, ensures that the user remains 
logged in while browsing your store. It only must be included within the `init` section.

```ini{3,4}
description = "Default Layout"

[mallDependencies]  // [!code focus]
[session] // [!code focus]
==
<!DOCTYPE html>
```

```twig{10}
description = "Default Layout"

[mallDependencies] 
[session]
==
<!DOCTYPE html>
<html>
<head>  // [!code focus]
    <!-- ... -->// [!code focus]
    {% component 'mallDependencies' %}  // [!code focus]
</head>  // [!code focus]
<body>
    <!-- ... -->
</body>
</html>
```

### Scripts

Make sure to include the `scripts` tag in your layout, which works as a placeholder for all injected 
JavaScript files and code blocks. 

The **Mall** plugin also requires Octobers [AJAX framework](https://octobercms.com/docs/ajax/introduction)
for the interaction with the PHP-side of the components. Thus, make sure to include the framework 
either by using one of the [Combiner aliases](./https://octobercms.com/docs/markup/filter-theme#combiner-aliases) 
or the `{% framework extras %}` tag, as shown below.

```twig{8,9}
<!DOCTYPE html>
<html>
<head>
    <!-- ... -->
</head>
<body>  // [!code focus]
    <!-- ... -->
    {% framework extras %}  // [!code focus]
    {% scripts %}  // [!code focus]
</body>  // [!code focus]
</html>
```

### RainLab.Pages Static Menu

If you are using the `RainLab.Pages` plugin, you can add the `All mall shop categories` entry to 
your navigation. This will render a tree of all available categories in your theme. 


## Theme: Pages

The **Mall** plugin expects some predefined pages from your theme to handle the different views. 
These pages are usually recognized automatically, but can be selected in the backend settings page,
under `Mall: General` -> `Configuration`.

The following pages are required for this, of course you can name them as you please.

- `product.htm` - Single Product
    - URL scheme: `/product/:slug/:variant?`
- `category.htm` - Category
    - URL scheme: `/category/:slug*`
- `address.htm` - Configure Customer Address
    - URL scheme: `/address/:address?/:redirect?/:set?`
- `checkout.htm` - (Quick) Checkout
    - URL scheme: `/checkout/:step?`
- `myaccount.htm` - Customer Account
    - URL scheme: `/account/:page?`
- `cart.htm` - Current Cart
    - URL scheme: `/cart`
- `login.htm` - User Login Page
    - URL scheme: `/login`




_WiP_


---------------

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
title = "Address"
url = "/address/:address?/:redirect?/:set?"
layout = "default"
is_hidden = 0

[session]
security = "user"
redirect = "home"

[addressForm]
address = "{{ :address }}"
redirect = "{{ :redirect }}"
set = "{{ :set }}"
==
{% component 'addressForm' %}
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

