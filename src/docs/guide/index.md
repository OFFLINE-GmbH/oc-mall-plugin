---
title: Getting Started
editLink: true
---

# Mall October CMS Plugin

Welcome to the official documentation of the **Mall** plugin, the all-inclusive e-commerce solution
for the Laravel-based OctoberCMS. 

On the following pages you will find all the information you need to install the plugin on your 
website and integrate it in your used theme. You will also read about the individual models, 
controllers and how you can extend them to integrate your own functions.

> [!NOTE]
> If you think, that some details are missing or not sufficiently well explained, we invite you to 
> extend and add this to our documentation. For this case you will find an "Edit this page on GitHub" 
> link at the bottom of each page, which brings you to the corresponding markdown file in our Github 
> repository. Thanks for your help!


## Requirements

We highly recommend using the latest OctoberCMS v3 and PHP v8 versions available, however, the 
following requirements represent the minimum needed to run Mall on your website.

- OctoberCMS v2.2+ | v3.0+
- PHP v7.4+ | v8.0+
- MySQL v5.7+ | v8.0+ **`or`** MariaDB v10.2+ **`or`** SQLite<span style="color: red;">**\***</span> v3.19+

> [!CAUTION]
> <span style="color: red;">**\***</span> Although it is possible to use Mall via SQLite, we 
> strongly recommend using one of the other database systems, especially on a large number of 
> products or customers.


### Dependencies

Mall relies on the following official OctoberCMS Plugins, which must be installed before installing 
the Mall extension itself.

- [RainLab.Location](https://octobercms.com/plugin/rainlab-location) v1.2+
- [RainLab.Translate](https://octobercms.com/plugin/rainlab-translate) v1.9+ | v2.0+
- [RainLab.User](https://octobercms.com/plugin/rainlab-user) v1.6+ | v2.0+

Mall also requires the following PHP packages, which should be installed automatically, when 
installing Mall via `plugin:install` or `project:sync` on OctoberCMS v3 installations.

- [barryvdh/laravel-dompdf](https://packagist.org/packages/barryvdh/laravel-dompdf) v1.0+ | v2.0+
- [hashids/hashids](https://packagist.org/packages/hashids/hashids) v5.0+
- [league/omnipay](https://packagist.org/packages/league/omnipay) v3.2+
- [omnipay/paypal](https://packagist.org/packages/omnipay/paypal) v3.0+
- [omnipay/stripe](https://packagist.org/packages/omnipay/stripe) v3.0+


### Optional dependencies

The following dependencies are only required if necessary, but must be installed manually if needed.

- [bummzack/omnipay-postfinance](https://packagist.org/packages/bummzack/omnipay-postfinance) - To use the PostFinance payment provider
- [offline/jsonq](https://packagist.org/packages/offline/jsonq) - To use the file based index
- [tmarois/filebase](https://packagist.org/packages/tmarois/filebase) - To use the file based index
- [vitalybaev/google-merchant-feed](https://packagist.org/packages/vitalybaev/google-merchant-feed) - To use the Google Merchant Feed integration


### Development dependencies

The following dependencies are only required when developing or extending the Mall plugin itself, and 
must be installed manually as well, of course.

- [fakerphp/faker](https://packagist.org/packages/fakerphp/faker) v1.23+
- [mockery/mockery](https://packagist.org/packages/mockery/mockery) v1.6+
- [phpunit/phpunit](https://packagist.org/packages/omnipay/stripe) v8.5+ | v9.0+
- [squizlabs/php_codesniffer](https://packagist.org/packages/squizlabs/php_codesniffer) v3.0+


## Links

- [Demo Website](https://mall.offline.swiss)
- Mall Plugin - [GitHub](https://github.com/OFFLINE-GmbH/oc-mall-plugin) / [Marketplace](https://octobercms.com/plugin/offline-mall)
- Demo Theme - [GitHub](https://github.com/OFFLINE-GmbH/oc-mall-theme) / [Marketplace](https://octobercms.com/theme/offline-oc-mall-theme)
