<?php namespace OFFLINE\Mall\Updates;

use Cache;
use October\Rain\Database\Model;
use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Classes\Registration\BootServiceContainer;
use OFFLINE\Mall\Classes\Registration\BootTwig;
use OFFLINE\Mall\Classes\Seeders\CategoryTableSeeder;
use OFFLINE\Mall\Classes\Seeders\CustomerGroupTableSeeder;
use OFFLINE\Mall\Classes\Seeders\CustomerTableSeeder;
use OFFLINE\Mall\Classes\Seeders\CustomFieldTableSeeder;
use OFFLINE\Mall\Classes\Seeders\NotificationTableSeeder;
use OFFLINE\Mall\Classes\Seeders\OrderStateTableSeeder;
use OFFLINE\Mall\Classes\Seeders\PaymentMethodTableSeeder;
use OFFLINE\Mall\Classes\Seeders\ProductTableSeeder;
use OFFLINE\Mall\Classes\Seeders\PropertyTableSeeder;
use OFFLINE\Mall\Classes\Seeders\ShippingMethodTableSeeder;
use OFFLINE\Mall\Classes\Seeders\TaxTableSeeder;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\PriceCategory;
use OFFLINE\Mall\Models\ReviewSettings;

class DatabaseSeeder extends Seeder
{
    public $app;

    use BootTwig;
    use BootServiceContainer;

    public function run()
    {
        $this->app = app();

        $this->registerTwigEnvironment();
        $this->registerServices();

        Model::unguard();
        Cache::clear();

        PriceCategory::create([
            'code' => 'old_price',
            'name' => 'Old price',
        ]);
        Currency::create([
            'is_default' => app()->runningUnitTests(),
            'code'       => 'CHF',
            'format'     => '{{ currency.code }} {{ price|number_format(2, ".", "\'") }}',
            'decimals'   => 2,
            'rate'       => 1,
        ]);
        Currency::create([
            'is_default' => ! app()->runningUnitTests(),
            'code'       => 'EUR',
            'format'     => '{{ price|number_format(2, ".", "\'") }}{{ currency.symbol }}',
            'decimals'   => 2,
            'symbol'     => '€',
            'rate'       => 1.14,
        ]);
        Currency::create([
            'is_default' => ! app()->runningUnitTests(),
            'code'       => 'USD',
            'format'     => '{{ currency.symbol }} {{ price|number_format(2, ".", "\'") }}',
            'decimals'   => 2,
            'symbol'     => '$',
            'rate'       => 1.02,
        ]);

        $this->call(CategoryTableSeeder::class);
        $this->call(TaxTableSeeder::class);
        $this->call(PaymentMethodTableSeeder::class);
        $this->call(ProductTableSeeder::class);
        $this->call(CustomFieldTableSeeder::class);
        $this->call(ShippingMethodTableSeeder::class);
        $this->call(CustomerGroupTableSeeder::class);
        $this->call(CustomerTableSeeder::class);
        $this->call(PropertyTableSeeder::class);
        $this->call(OrderStateTableSeeder::class);
        $this->call(NotificationTableSeeder::class);

        ReviewSettings::set('enabled', true);
    }
}
