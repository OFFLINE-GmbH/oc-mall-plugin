<script setup>
import BadgeStd from '../../.vitepress/components/BadgeStd.vue'
import SpoilerStd from '../../.vitepress/components/SpoilerStd.vue'
</script>

# Latest Release

<BadgeStd label="v3.2.1 - Stable" color="tip" />

With version 3.2, Mall is looking to the future. Fixing old bugs, removing legacy code and issues,
but also taking the first cautious steps forward. The dust is knocked off, the gaze is directed 
towards the rising sun.


## Version 3.2.1

- Fix Release Number


## Version 3.2.0

- Added new navigation icons for catalogue and orders menus.
- Added new `<grp>_<idx>_<action>_<name>.php` migration file naming for a better overview.
- Added new enabled-state to currency + minor changes + tests.
- Added new enabled-state and additional title property to price category + minor changes + tests.
- Added new enabled-state to taxes + minor changes + tests.
- Added new enabled and default-states to payment methods + minor changes + tests.
- Added new enabled and default-states to shipping methods + minor changes + tests.
- Added new enabled-state to order states + minor changes.
- Added a new IsStates database model trait, to support is_default and is_enabled behaviors.
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
- Fixed wrong indexed value key on PropertyValue using color types, thanks to @toome123.
- Removed legacy v1 code.
- `030_01-update_system_plugin_history.php`
- `030_02-alter_offline_mall_currencies.php`
- `030_03-alter_offline_mall_price_categories.php`
- `030_04-alter_offline_mall_taxes.php`
- `030_05-alter_offline_mall_payment_methods.php`
- `030_06-alter_offline_mall_shipping_methods.php`
- `030_07-alter_offline_mall_order_states.php`
