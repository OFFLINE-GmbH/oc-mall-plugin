<p align="center"> 
	<img style="max-width: 100%; margin: 2rem auto; display: block;" src="https://user-images.githubusercontent.com/8600029/52163618-c3bf3d80-26e4-11e9-870c-427401a27937.jpeg">
</p>


# oc-mall

> E-commerce solution for October CMS

[![Build Status](https://travis-ci.org/OFFLINE-GmbH/oc-mall-plugin.svg?branch=develop)](https://travis-ci.org/OFFLINE-GmbH/oc-mall-plugin)

`oc-mall` is a fully featured online shop solution for October CMS.

* Manage Products and Variants
* Virtual products (download only, no shipping)
* Product services (e. g. extended warranty, on-site installation)
* Stock management
* Wishlists
* Reviews and ratings
* Checkout via Stripe, PayPal and PostFinance supported out-of-the-box
* Custom payment providers 
* Integrated with RainLab.User
* Multi-currency and multi-language (integrates with RainLab.Translate)
* Shipping and Tax management
* Specific prices for different customer groups
* Unlimited additional price fields (reseller, retail, reduced, etc)
* Custom order states
* Flexible e-mail notifications
* Easily extendable with custom features
* [Google Tag Manager and Google Merchant Center integrations](https://offline-gmbh.github.io/oc-mall-plugin/digging-deeper/analytics.html)

#### Documentation
The documentation of this plugin can be found here:
[https://offline-gmbh.github.io/oc-mall-plugin/](https://offline-gmbh.github.io/oc-mall-plugin/)

#### Requirements

* PHP7.2+
* October Build 444+
* For best performance use MySQL 5.7+ or MariaDB 10.2+

#### Demo

A live demo of the plugin can be found here:
[https://mall.offline.swiss](https://mall.offline.swiss)

#### Support

For support and development requests please file an issue on GitHub.

## Installation

The easiest way to get you started is by using the command line:

```bash
php artisan plugin:install rainlab.user
php artisan plugin:install rainlab.location
php artisan plugin:install rainlab.translate
php artisan plugin:install offline.mall
``` 

Once the plugin is installed take a look at
[the official documentation](https://offline-gmbh.github.io/oc-mall-plugin/)
to get everything up and running.

## Benchmarks

Below are some totally unscientific benchmarks created on a lazy Saturday afternoon. 
These tests were run on a DigitalOcean CPU optimized Droplet with 2 vCPU and 4GB RAM.
October was run on Ubuntu 18.04, PHP 7.2.10, Apache 2.4.19 and MySQL 5.7.24.

All measurements were done using the [Bedard.Debugbar](https://octobercms.com/plugin/bedard-debugbar) 
plugin and are the average load time over 10 page loads (I told you they were unscientific!).
 
`Index size` defines the size of the `offline_mall_index` table. This table includes de-normalized 
information about all Products and Variants. An index size of 1000 means there are 1000 
individual Variants and Products stored. The demo data used was built using the 
 `php artisan mall:seed-demo` command run in an infinite loop.

`Category page load` is the page load time measured when a category page is loaded. 
All stored products will be filtered, sorted (by sales count) and counted by the currently viewed `category_id`.
Nine of these products will be displayed and the pagination will be built based on the returned number
of results.

`Filtered page load` is the page load time measured when two filters are being enabled
 (filter by the color `Red` and the material `Carbon`). In this case all products
will be filtered by their category, their color and their material. The pagination
will be built based on the returned number of results.

| Index size | Category page load | Filtered page load |
| ---------: | -----------------: | -----------------: |
|      1'000 |             290 ms |             281 ms |
|      5'000 |             301 ms |             295 ms |
|     10'000 |             324 ms |             318 ms |
|     50'000 |             448 ms |             433 ms |
|    100'000 |             586 ms |             570 ms |
|    200'000 |             912 ms |             865 ms |
|    300'000 |            1300 ms |            1240 ms |

Please be aware that these benchmarks are only here to show you how this plugin
behaves under different loads and the times will vary depending on the
hardware, configuration and setup of your installation. If you really want to know how well
the plugin performs install it yourself and give it a go!


## Contributing

### Documentation

The raw documentation for this plugin is stored in the docs directory. It is written in markdown and built with 
[VuePress](https://vuepress.vuejs.org/).

For a live preview of the docs install `vuepress` locally and run `vuepress dev` from the docs directory.

### Bugs and feature requests

If you found a bug or want to request a feature please file a GitHub issue.

### Pull requests

PRs are always welcome! Open them against the `develop` branch.
If you plan a time consuming contribution please open an issue first and describe what changes you have in mind. 
