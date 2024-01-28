---
title: Installation
editLink: true
---

# Installation

**Mall** can be found on the official [October CMS Marketplace](https://octobercms.com/plugin/offline-mall). 


## Using Marketplace

The easiest way to install **Mall** is via the official Marketplace. There you can add the plugin to 
your project and install it by executing the following artisan command in the root directory of your 
website:

```sh
php artisan project:sync
```

During this installation, all dependencies are downloaded and installed automatically.


## Manual Installation

You can install **Mall** also manually like any other OctoberCMS plugin. However, make sure that you 
install the dependencies up-front. Visit the root directory of your OctoberCMS website and execute 
the following commands

```sh
php artisan plugin:install RainLab.User
php artisan plugin:install RainLab.Location
php artisan plugin:install RainLab.Translate
php artisan plugin:install OFFLINE.Mall
```

You can also use composer to install the plugins, however, keep in mind that you've to install 
and migrate the dependent packages first before installing and migrating Mall itself.

```sh
composer require rainlab/user-plugin
composer require rainlab/location-plugin
composer require rainlab/translate-plugin
```

```sh
php artisan october:migrate
```

```sh
composer require offline/oc-mall-plugin
```

```sh
php artisan october:migrate
```

Both ways will automatically install all composer packages required by the **Mall** plugin, at least 
on OctoberCMS v3 installations. 


## Optional Dependencies

Certain features of the **Mall** plugin requires you to install additional composer packages to 
make everything work. 

| Feature                      | Package |
| ---------------------------- | ------- |
| File-Based Index             | [`offline/jsonq`](https://packagist.org/packages/offline/jsonq)<br />[`tmarois/filebase`](https://packagist.org/packages/tmarois/filebase) |
| Google Merchant Feed         | [`vitalybaev/google-merchant-feed`](https://packagist.org/packages/vitalybaev/google-merchant-feed) |
| PostFinance Payment Provider | [`bummzack/omnipay-postfinance`](https://packagist.org/packages/bummzack/omnipay-postfinance) |


Simple use the following command in the root directory of your OctoberCMS website to install the 
desired package.

```sh
composer require <$package_name>
```

## Check your Installation

After you successfully installed all plugins and packages, you can run the `mall:check` artisan 
command to validate your installation. 

**Don't panic!** After the initial installation there will be some items that are marked as FAIL. 
This is because we didn't configure the plugin yet.

```sh
php artisan mall:check
```

::: tip
You can run this command at any time in the future to make sure everything is set up correctly.
:::


## Seed initial data

Since version 3.1, **Mall** no longer inserts any data automatically during the installation. You 
can use the following command to seed the initial data, as happened before.

```sh
php artisan plugin:seed OFFLINE.Mall OFFLINE\Mall\Updates\Seeders\MallDatabaseSeeder
```

However, we **highly recommend** using the following command instead, which allows you to customize 
the desired data.

```sh
php artisan mall:seed
```

::: danger
Please note that this command **deletes** all existing store data and resets all setting. You should 
therefore not run this command on an already configured installations.
:::

You can also simple reset the whole **Mall** installation by running the following command.

```sh
php artisan plugin:refresh OFFLINE.Mall
```


## Setup Demo-Theme

We also provide a simple demonstration theme to make getting started with our **Mall** plugin as 
easy as possible. You can find this theme on the official [October CMS Marketplace](https://octobercms.com/theme/offline-oc-mall-theme) 
as well as on [GitHub](https://github.com/OFFLINE-GmbH/oc-mall-theme).

To install the theme, make sure you've installed the dependent packages first

```sh
php artisan plugin:install RainLab.Pages
php artisan plugin:install OFFLINE.SiteSearch
```

After that, you can download and extract the theme inside the `themes/mala` folder of your 
OctoberCMS website and select it as your active theme.






## Configuration

_WiP_

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

<style module>
table {
    width: 100%;
    disable: table;
}
</style>