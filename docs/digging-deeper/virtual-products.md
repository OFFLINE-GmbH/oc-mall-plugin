# Virtual products

If a product is not something physical (like a file), you can mark it as "virtual".

Virtual products are offered to the customer as a download. After a payment
for an order with virtual products has been processed, a mail with personal download
links is sent to the customer.

If an order consist only of virtual products, the shipping step is skipped during
checkout.

A virtual product may consist of a file uploaded by the site admin. In this case
each customer receives the same file (for example an e-book).

There is also the possibility to [listen for an event where a customer specific
file can be attached](#generate-user-specific-product-files) to a virtual product (for example a generated gift card).

## Product files

Each virtual product can have exactly one file attached. This is the file that
is sent to the user once the product is paid.

Newer versions of this file can be uploaded at any time. This way the user can 
download the latest version of the product even after the initial purchase &ndash; given that 
the download grant has not expired.

The user can only ever download the latest file version by default.

## Product file grants

When a customer has paid for a virtual product, a 
product file grant is created. The grant allows the customer to download
the purchased file for a specified number of days and a specified number of times. 

It is also possible to require the user to be logged in when the download
link is visited. If this option is not set, any visitor that knows the download link
can download the file.

## Generate user specific product files

You may want to generate a specific file for a customer when a virtual product is bought 
(for example a personal concert ticket or a gift card).

To do this, listen for the `mall.product.file_grant.created` event and attach your custom
file to the provided `ProductFileGrant` model.

The easiest way to implement this feature is to create a custom plugin and register
the event in the plugin's boot method.

You can find an example on how to generate custom gift cards below
 ([or in this gist](https://gist.github.com/tobias-kuendig/49ec99fc080fc6ba3824024f965eeafc)). 

```php
<?php namespace OFFLINE\MallGiftCards;

use Event;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ProductFileGrant;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function boot()
    {
        /**
         * Listen for created download grants.
         * Create a discount code, generate a gift card PDF,
         * then attach it to the grant.
         */
        Event::listen('mall.product.file_grant.created', function (ProductFileGrant $grant, Product $product) {
            // Make sure the event is triggered by a product you care about. Validate this using the slug, the
            // assigned category or one of the other unique values (user_defined_id, gtin, mpn, etc).
            // In this example we check if the product belongs to a category that has the
            // "generate-gift-cards" code set as a custom code.
            $isGiftCardCategory = $product->categories->contains(function (Category $category) {
                return $category->code === 'generate-gift-cards';
            });
            // Exit here if this is not a product with a gift card category attached.
            if ( ! $isGiftCardCategory) {
                return;
            }

            // Create a new discount model.
            $discount = Discount::create([
                // Generate a descriptive name for the discount using the product's price information.
                'name'                 => sprintf('%s Gift Card', $product->price()->string),
                // Make sure it can only be used once.
                'max_number_of_usages' => 1,
                // Re-use the grant's expiry date.
                'expires'              => $grant->expires_at,
                // The discount is triggered by a code. The code will be created automatically on save.
                'trigger'              => 'code',
                // The discount is of a fixed amount. The amount will be defined below.
                'type'                 => 'fixed_amount',
            ]);

            // Set the amounts relation for each currency.
            Currency::getAll()->each(function (Currency $currency) use ($discount, $product) {
                $discount->amounts()->save(new Price([
                    'currency_id' => $currency->id,
                    'field'       => 'amounts',
                    // Re-use the price information form the product itself.
                    'price'       => $product->price($currency)->float,
                ]));
            });

            // Generate a gift card PDF (or image). We're creating a text file here for simplicity.
            $path = storage_path(sprintf('app/gift_card_%s.txt', $grant->id));
            file_put_contents($path, 'Your coupon code is: ' . $discount->code);

            // Attach the created file to the ProductFileGrant. This makes sure your custom file
            // will be downloaded and not the file that is attached to the $product itself.
            $grant->file = $path;
            // Set a custom display name for the grant.
            $grant->display_name = $discount->name;
            // Don't forget to save the changes!
            $grant->save();
        });
    }
}
```