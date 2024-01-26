<?php declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Updates\Seeders\Tables\PriceCategoryTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\CurrencyTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\CategoryTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\TaxTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\PaymentMethodTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\ProductTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\CustomFieldTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\ShippingMethodTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\CustomerGroupTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\CustomerTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\PropertyTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\OrderStateTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\NotificationTableSeeder;

class MallDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        $this->call(PriceCategoryTableSeeder::class);
        $this->call(CurrencyTableSeeder::class);
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
    }
}
