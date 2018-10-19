# VuePress


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
