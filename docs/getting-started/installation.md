---
sidebarDepth: 3
next: /getting-started/pages-setup
---

# Getting started

## Installation

The plugin can be found on the official [October CMS Marketplace](https://octobercms.com/plugin/offline-mall). You 
can install it via the Projects feature of the Marketplace itself or via your installation's backend settings.

The `OFFLINE.Mall` plugin depends on `RainLab.User` and `RainLab.Location`

The easiest way to get you started is by using the command line:

```bash
php artisan plugin:install rainlab.user
php artisan plugin:install rainlab.location
php artisan plugin:install offline.mall
``` 

### Checking your installation

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

To get a feeling for how `oc-mall` works you can run the following command to pre-populate your installation with 
demo data. 

```bash
php artisan mall:seed-demo
```

::: warning
This will erase all shop data and reset all settings! Do not run this command if you have already configured your 
installation. 
:::

You can always revert to a blank installation by running

```bash
php artisan plugin:refresh offline.mall
```

## Configuration

Once your installation is complete follow the configuration steps below.

### Locations

Visit `Backend settings -> Location -> Countries & States`. Make sure only the countries that you are shipping to are
 enabled. Disable every other country in the list. 


### Pages

`oc-mall` expects your website to have a few predefined pages to show product or order details. These pages have to be 
selected via the backend settings page.

#### Create pages

First, create the following CMS pages in your theme. You can name them as you wish, just make sure to include the same 
URL parameters as shown below.

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
You can find example contents for each of these pages in the [Pages Setup](./pages-setup.md) section. 
:::
 
#### Link pages

Once you have created all pages, go to `Backend settings -> Mall: General -> General settings` and select them 
in the respective dropdown field.

::: tip
If you are not sure on how to create these pages take a look at the
[oc-mall-theme](https://github.com/OFFLINE-GmbH/oc-mall-theme) theme for reference. 
:::

### Currencies

Visit `Backend settings -> Mall: General -> Currencies`. Here you find a list of all currencies your installation 
supports. Create, edit or delete them as you need. 

You can find detailed documentation on currencies in the [Currencies Section](../digging-deeper/currencies.md).


### Taxes

Visit `Backend settings -> Mall: General -> Taxes`. Here you find a list of all available tax rates.
Create, edit or delete them as you need. 

You can find detailed documentation on taxes in the [Taxes Section](../digging-deeper/taxes.md).