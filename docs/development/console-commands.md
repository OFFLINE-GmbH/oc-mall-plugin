# Console commands

## `mall:check`

The `php artisan mall:check` console command runs a set of checks on your October installation and makes sure that 
everything is configured correctly. It also supports you in fixing any problems that were found.

## `mall:seed-demo`

The `php artisan mall:seed-demo` command will populate your installation with demo data.


::: warning
This will erase all shop data and reset all settings! Do not run this command if you have already configured your 
installation. 
:::

## `mall:init`

The `php artisan mall:init` command will remove all order and customer data from 
your installation. Products, Variants and your Settings will remain untouched.
This command essentially brings the installation back to the 
state it had before the first customer created an account.

The command is useful to revert back to a clean slate once you've installed
and tested your oc-mall installation on a production system. 


::: warning
This will erase all order and customer data! Do not run this command if you have already 
opened your shop up to the public. 
:::

## `mall:reindex`

The `php artisan mall:reindex` command re-indexes all your product data and fixes a corrupted product index.

You will notice a corrupted index if your category listing shows
outdated data or the product filters are 
not working as expected. 

::: warning
During the re-indexing process your frontend will be wiped clean and 
only show already re-indexed products. A re-indexing of a small 
installation will only take a few seconds. Be aware that this process 
might take a few minutes if you re-index a huge catalogue with thousands 
of products.
:::
