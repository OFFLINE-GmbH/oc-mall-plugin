---
sidebarDepth: 3
next: /getting-started/theme-setup
---

# Getting started

## Installation

The plugin can be found on the official [October CMS Marketplace](https://octobercms.com/plugin/offline-mall). You 
can install it via the Projects feature of the Marketplace itself or via your installation's backend settings.

The `OFFLINE.Mall` plugin depends on `RainLab.User`, `RainLab.Location`
and `RainLab.Translate`.

The easiest way to get you started is by using the command line:

```bash
php artisan plugin:install rainlab.user
php artisan plugin:install rainlab.location
php artisan plugin:install rainlab.translate
php artisan plugin:install offline.mall
```

If you plan to use our [demo theme](https://github.com/OFFLINE-GmbH/oc-mall-theme) make sure to also
 install `RainLab.Pages`. These are dependencies of the demo theme, not the plugin itself.

```bash
# For the demo theme only!
php artisan plugin:install rainlab.pages
```   

### Check your installation

After the plugin has been successfully installed you can run the `mall:check` command to validate your installation. 

```bash
php artisan mall:check
```

::: tip
You can run this command at any time in the future to make sure everything is set up correctly.
:::

**Don't panic!** After the initial installation there will be some items that are marked as `FAIL`. This is because 
we didn't configure the plugin yet.  

## Demo data

To get a feeling for how `oc-mall` works, you can run the following command to pre-populate your installation with 
demo data. 

```bash
php artisan mall:seed-demo
```

::: warning
This will erase all shop data and reset all settings! Do not run this command if you have already configured your 
installation. 
:::

You can always revert back to a blank installation by running

```bash
php artisan plugin:refresh offline.mall
```

## Demo theme

To make getting started with `oc-mall `as easy as possible, you can find a demo implementation of a shop
theme on GitHub: [https://github.com/OFFLINE-GmbH/oc-mall-theme](https://github.com/OFFLINE-GmbH/oc-mall-theme)

::: warning
If you use the demo theme you should still apply the steps mentioned in the "Configuration" section.
:::

* <input type="checkbox"> Simply clone the theme to `<your installation>/themes/mall` and select it as your active theme.

* <input type="checkbox"> The demo theme requires `RainLab.Translate` and `RainLab.Pages` to be installed. Make sure 
these plugins are available as well.

If you want to start with a blank slate just follow the instructions on this and the [Theme Setup](./theme-setup.md) 
page to get everything up and running.
 

## Configuration

Once your installation is complete, follow the configuration steps below.

### Locations

Visit `Backend settings -> Location -> Countries & States`. Make sure that
only countries you are shipping to are enabled. Disable every other
country in the list. 


### Pages

`oc-mall` expects your website to have a few predefined pages to show
product, account or order details. These pages have to be 
selected via the backend settings page.

#### Create pages

First, create the following CMS pages in your theme. You can name them as you wish, just make sure to include the same 
URL parameters as shown below.

You can create them without any additional markup. We will populate 
them in the upcomming [Theme Setup](./theme-setup.md) step.

| File              | Url                                   |
| ----------------- | ------------------------------------- | 
| `product.htm`     | `/product/:slug/:variant?`            |
| `category.htm`    | `/category/:slug*`                    |
| `address.htm`     | `/address/:address?/:redirect?/:set?` |
| `checkout.htm`    | `/checkout/:step?`                    |
| `myaccount.htm`   | `/account/:page?`                     |
| `cart.htm`        | `/cart`                               |
| `login.htm`       | `/login`                              |


::: tip
You can find example contents for each of these pages in the
[Theme Setup](./theme-setup.md) section. 
:::
 
#### Link pages

Once you have created all pages, go to `Backend settings -> Mall: General -> General settings` and select them 
in the respective dropdown field.

::: tip
If you are not sure on how to create these pages take a look at the
[oc-mall-theme](https://github.com/OFFLINE-GmbH/oc-mall-theme) for reference. 
:::

### Currencies

Visit `Backend settings -> Mall: General -> Currencies`. Here you find a list of all currencies your installation 
supports. Create, edit or delete them as you need. 

You can find detailed documentation on currencies in the [Currencies Section](../digging-deeper/currencies.md).


### Taxes

Visit `Backend settings -> Mall: General -> Taxes`. Here you find a list of all available tax rates.
Create, edit or delete them as you need. 

You can find detailed documentation on taxes in the [Taxes Section](../digging-deeper/taxes.md).