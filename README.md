# oc-mall
> E-commerce solution for October CMS 

[![Build Status](https://travis-ci.org/OFFLINE-GmbH/oc-mall-plugin.svg?branch=develop)](https://travis-ci.org/OFFLINE-GmbH/oc-mall-plugin)

**This plugin is under heavy development**. Don't use it in production yet. A first production ready version is  
planned to be released towards the end of 2018.


## Seed demo data

```bash
# All your existing data will be erased!
php artisan mall:seed-demo
```

## Access product images

You can use the following methods to access product images:

```php
// Get the first image of the main image set
$product->main_image;

// Get all images except the main image
$product->images;

// Get all available images, including the main image
$product->all_images;
```
