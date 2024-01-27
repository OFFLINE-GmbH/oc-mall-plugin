# Going Live

Once everything is set up and you have deployed your oc-mall installation to a production server you, can
use the checklist on this page to make sure everything is running smoothly.

::: tip
If you want to reset your installation back to a clean slate after verifying it in production, simply
run [`php artisan mall:init`](../development/console-commands.md#mall-init) to remove your test accounts and 
test orders. 
:::

* <input type="checkbox"> `php artisan mall:check` returns no errors
* <input type="checkbox"> October's mail settings are set up correctly (the production driver is set) 
* <input type="checkbox"> There is no `mail.to` configuration entry in `config/mail.php` 
* <input type="checkbox"> Unused mail notifications are disabled under Backend Settings -> Mall: General 
-> Notifications 
* <input type="checkbox"> All mail templates are styled correctly
* <input type="checkbox"> API keys for all payment providers are set up correctly
* <input type="checkbox"> Customer sign up works
* <input type="checkbox"> Customer sign in works
* <input type="checkbox"> Checking out with all available payment providers works (perform a test purchase!)
* <input type="checkbox"> All relevant countries are enabled under Backend Settings -> Location -> Countries & States
* <input type="checkbox"> Relevant countries are pinned
* <input type="checkbox"> All relevant products and variants are published