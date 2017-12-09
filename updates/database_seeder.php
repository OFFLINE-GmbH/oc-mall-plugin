<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Model;
use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Classes\Seeders\CustomerTableSeeder;
use OFFLINE\Mall\Classes\Seeders\CustomFieldTableSeeder;
use OFFLINE\Mall\Classes\Seeders\ProductTableSeeder;
use OFFLINE\Mall\Classes\Seeders\ShippingMethodTableSeeder;
use OFFLINE\Mall\Classes\Seeders\TaxTableSeeder;
use OFFLINE\Mall\Classes\Seeders\CountriesTableSeeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();
        $this->call(TaxTableSeeder::class);
        $this->call(ProductTableSeeder::class);
        $this->call(CustomFieldTableSeeder::class);
        $this->call(ShippingMethodTableSeeder::class);
        $this->call(CountriesTableSeeder::class);
        $this->call(CustomerTableSeeder::class);
    }
}
