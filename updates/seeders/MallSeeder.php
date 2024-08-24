<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Updates\Seeders\Tables\CategoryTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\CurrencyTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\NotificationTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\OrderStateTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\PaymentMethodTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\PriceCategoryTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\PropertyTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\ShippingMethodTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\TaxTableSeeder;

class MallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        $this->call([
            PriceCategoryTableSeeder::class,
            CurrencyTableSeeder::class,
            CategoryTableSeeder::class,
            TaxTableSeeder::class,
            PaymentMethodTableSeeder::class,
            ShippingMethodTableSeeder::class,
            PropertyTableSeeder::class,
            OrderStateTableSeeder::class,
            NotificationTableSeeder::class,
        ]);
    }
}
