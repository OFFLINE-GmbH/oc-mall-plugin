<p align="center"> 
	<img style="max-width: 100%; margin: 2rem auto; display: block;" src="https://user-images.githubusercontent.com/8600029/52163618-c3bf3d80-26e4-11e9-870c-427401a27937.jpeg">
</p>


# Mall

> The all-inclusive e-commerce solution of OctoberCMS.

**Mall** is a fully featured online shop solution for October CMS.

- Manage Products and Variants
- Virtual products (download only, no shipping)
- Product services (e. g. extended warranty, on-site installation)
- Stock management
- Wishlists
- Reviews and ratings
- Checkout via Stripe, PayPal and PostFinance supported out-of-the-box
- Custom payment providers 
- Integrated with RainLab.User
- Multi-currency and multi-language (integrates with RainLab.Translate)
- Shipping and Tax management
- Specific prices for different customer groups
- Unlimited additional price fields (reseller, retail, reduced, etc)
- Custom order states
- Flexible e-mail notifications
- Easily extendable with custom features
- [Google Tag Manager and Google Merchant Center integrations](https://offline-gmbh.github.io/oc-mall-plugin/digging-deeper/analytics.html)


## Read More

- [Mall on OctoberCMS Marketplace](https://octobercms.com/plugin/offline-mall)
- [Mall Demo Theme on OctoberCMS Marketplace](https://octobercms.com/theme/offline-oc-mall-theme)
- [Official Documentation](https://offline-gmbh.github.io/oc-mall-plugin)
- [Demonstration Website](https://mall.offline.swiss)


## Requirements

- PHP 7.4+ | 8.0+
- OctoberCMS 2.2+ | v3.0+
- MySQL 5.7+ | v8.0+ or MariaDB v10.2+ or SQLite v3.19+

We highly recommend not using SQLite on production environments, especially for larger Shops.

There is also a [legacy version](https://github.com/OFFLINE-GmbH/oc-mall-plugin/tree/v1) of this 
plugin available, that works with OctoberCMS v1. However, this version is no longer updated nor 
supported.


## Support

For support and development requests please file an issue on GitHub.


## Installation

The easiest way to get you started is by using the command line:

```bash
composer require \
   rainlab/user-plugin \
   rainlab/location-plugin \
   rainlab/translate-plugin \
   offline/oc-mall-plugin
``` 

Once the plugin is installed take a look at [the official documentation](https://offline-gmbh.github.io/oc-mall-plugin/)
to get everything up and running.


## Benchmarks

Below are some totally unscientific benchmarks created on a lazy Saturday afternoon. These tests 
were run on a DigitalOcean CPU optimized Droplet with 2 vCPU and 4GB RAM. October was run on Ubuntu 1
8.04, PHP 7.2.10, Apache 2.4.19 and MySQL 5.7.24.

All measurements were done using the [Bedard.Debugbar](https://octobercms.com/plugin/bedard-debugbar) 
plugin and are the average load time over 10 page loads (I told you they were unscientific!).
 
`Index size` defines the size of the `offline_mall_index` table. This table includes de-normalized 
information about all Products and Variants. An index size of 1000 means there are 1000 individual 
Variants and Products stored. The demo data used was built using the  `php artisan mall:seed-demo` 
command run in an infinite loop.

`Category page load` is the page load time measured when a category page is loaded. All stored 
products will be filtered, sorted (by sales count) and counted by the currently viewed `category_id`.
Nine of these products will be displayed and the pagination will be built based on the returned 
number of results.

`Filtered page load` is the page load time measured when two filters are being enabled (filter by 
the color `Red` and the material `Carbon`). In this case all products will be filtered by their 
category, their color and their material. The pagination will be built based on the returned number 
of results.

| Index size | Category page load | Filtered page load |
| ---------: | -----------------: | -----------------: |
|      1'000 |             290 ms |             281 ms |
|      5'000 |             301 ms |             295 ms |
|     10'000 |             324 ms |             318 ms |
|     50'000 |             448 ms |             433 ms |
|    100'000 |             586 ms |             570 ms |
|    200'000 |             912 ms |             865 ms |
|    300'000 |            1300 ms |            1240 ms |

Please be aware that these benchmarks are only here to show you how this plugin behaves under 
different loads and the times will vary depending on the hardware, configuration and setup of your 
installation. If you really want to know how well the plugin performs install it yourself and give 
it a go!


## Contributing

### Documentation

The raw documentation for this plugin is stored in the `src/docs` directory. It is written in 
markdown and Vue and built with [VitePress](https://vitepress.dev).

for a live preview of the documentation, visit the root plugin directory, install the dependencies 
using `npm i` (ensure node.js and npm is installed on your machine first) and run the local server 
using `npm run docs:dev`.


### Bugs and feature requests

If you found a bug or want to request a feature please file a GitHub issue.


### Pull requests

PRs are always welcome! Open them against the `next` branch. If you plan a time consuming 
contribution please open an issue first and describe what changes you have in mind. 
