<?php declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Tax;

class TaxTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        $method = new Tax();
        $method->name = 'Default';
        $method->percentage = 8;
        $method->save();
    }
}
