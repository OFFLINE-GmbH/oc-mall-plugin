<script setup>
import BadgeStd from '../../.vitepress/components/BadgeStd.vue'
import SpoilerStd from '../../.vitepress/components/SpoilerStd.vue'
import CatalogueIcon from '../../.vitepress/components/CatalogueIcon.vue'
import OrdersIcon from '../../.vitepress/components/OrdersIcon.vue'
</script>

# Latest Release

<BadgeStd label="v3.2.1 - Stable" color="tip" />

> With version 3.2, Mall is looking to the future. Fixing old bugs, removing legacy code and issues,
> but also taking the first cautious steps forward. The dust is knocked off, the gaze is directed 
> towards the rising sun.

## Highlights

- [New navigation icons](#new-navigation-icons)
- [New documentation page](#new-documentation-page)
- [Introducing new PriceBag Calculator](#introducing-new-pricebag-calculator)
- [Enabled-States on different models](#enabled-states-on-different-models)
- [Updated console commands with new seeders](#updated-console-commands-with-new-seeders)

### New navigation icons

Using custom vectors graphics instead of FontAwesome / October Icons. Created with Affinity Designer, 
the source files are located in theresources directory.

<table><tr>
    <td style="padding:2rem;"><CatalogueIcon /></td>
    <td style="padding:2rem;"><OrdersIcon /></td>
</tr></table>

### New documentation page

The new documentation page, which you are probably reading right now, is now based on VitePress, 
the (in)direct successor to VuePress, and therefore also comes with a completely new look. While 
most of the documentation pages are still work in progress, progress is being made at breakneck 
speed.

### Introducing new PriceBag Calculator

In the first step, the new `PriceBag` calculation collector works - more or less - just alongside 
the current `TotalsCalculator` implementation (even if `PriceBag` already performs most of the 
calculations within `TotalsCalculator` itself). However, during the upcoming releases, `PriceBag` 
will be further expanded, designed to be more bullet-proof and performant until it will fully 
replace the whole `TotalsCalculator` bundle (including all related classes). In version 4.0.0 at the 
latest, but probably sooner.

### Enabled-States on different models

The following models use the new `IsState` model trait, which introduces an additional `is_enabled` 
column and controls the `is_default` one in a common manner as well. This means that the individual 
records can now be de- and activated separately and do not have to be deleted.

- Currency
- PriceCategory
- Tax
- Payment Methods
- Shipping Methods
- Order States

### Updated console commands with new seeders

The artisan console-commands have been revised and renamed, and adapted to the new seeders. The DB 
Seeders itself has been split up into the initial core-records (`OFFLINE\Mall\Updates\Seeders\CoreSeeder`) 
and the demonstration ones (`OFFLINE\Mall\Updates\Seeders\DemoSeeder`) and can also be called using 
Octobers' native `plugin:seed` command. The demonstration data has also been translated into german.

```sh
 mall
  mall:check            # Check if your setup is complete
  mall:index            # Recreate the general product index
  mall:purge            # Purge all customer and order related data
  mall:seed             # Seed the Mall related database records
```

**The new `mall:seed` command in detail**

```sh
> php artisan mall:seed --help

Description:
  Seed the Mall related database records.

Usage:
  mall:seed [options]

Options:
      --force            Don't ask before erasing all records
  -d, --with-demo        Insert demonstration records, such as products
  -l, --locale[=LOCALE]  Force a specific locale for the seeded records
```

## Full Changelog

### Version 3.2.1

<SpoilerStd label="Changes">

- Fix Release Number

</SpoilerStd>


### Version 3.2.0

<SpoilerStd label="Changes">

- Decreased PHP Requirement to 7.4 to re-support OC v2.2 installations.
- Added new navigation icons for catalogue and orders menus.
- Added new `<grp>_<idx>_<action>_<name>.php` migration file naming for a better overview.
- Added new enabled-state to currency + minor changes + tests.
- Added new enabled-state and additional title property to price category + minor changes + tests.
- Added new enabled-state to taxes + minor changes + tests.
- Added new enabled and default-states to payment methods + minor changes + tests.
- Added new enabled and default-states to shipping methods + minor changes + tests.
- Added new enabled-state to order states + minor changes.
- Added a new IsStates database model trait, to support is_default and is_enabled behaviors.
- Added a new PriceBag totals construct and handling.
- Added a new event to manipulate shipping country on FilteredTaxes trait, thanks to @Cryden.
- Updated console commands and database seeders.
- Updated demonstration content (+ add support for german translations).
- Updated available rounding options on Currency model to support extensions.
- Catch errors on HashIds trait + add typings.
- General code cleanup, especially on backend controllers and models.
- Replaced migration seeder in favor of Laravels / Octobers database seeders.
- Replaced old ReorderController behavior with ListController configuration.
- Replaced VuePress documentation with VitePress + refactored introduction pages.
- Fixed missing quickCheckout pages on GeneralSettings > Checkout page select field.
- Fixed missing translation strings.
- Fixed show OrderProducts on Orderlist -> ProductList component using the same currency as order itself.
- Fixed PHPUnit testing environment and tests.
- Fixed Wishlist component with no id parameter set.
- Fixed faulty empty-check that prevented indexing at 0 (int and string) values, thanks to @xyz1123581321.
- Fixed out of stock message even if stock was available, thanks to @xyz1123581321.
- Fixed wrong indexed value key on PropertyValue using color types, thanks to @toome123.
- Fixed wrong taxes calculation when discounts has been applied.
- Fixed wrong taxes calculation when different discounts has been applied to products and shipping costs.
- Fixed bad SQL error when addresses has been added to a customer profile on the backend.
- Fixed duplication mess on products with variants.
- Fixed possibility to take-over email address on CustomerProfile component, thanks to @cyril-design.
- Fixed products / variants disappear on first page view after editing a product.
- Removed legacy v1 code.
- `30_01-update_system_plugin_history.php`
- `30_02-alter_offline_mall_currencies.php`
- `30_03-alter_offline_mall_price_categories.php`
- `30_04-alter_offline_mall_taxes.php`
- `30_05-alter_offline_mall_payment_methods.php`
- `30_06-alter_offline_mall_shipping_methods.php`
- `30_07-alter_offline_mall_order_states.php`

</SpoilerStd>
