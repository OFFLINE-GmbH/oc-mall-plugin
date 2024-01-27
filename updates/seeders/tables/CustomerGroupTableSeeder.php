<?php declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\CustomerGroup;

class CustomerGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        if (app()->environment() != 'testing') {
            return;
        }

        $group = new CustomerGroup();
        $group->name = 'Gold Partners';
        $group->code = 'gold';
        $group->save();

        $group = new CustomerGroup();
        $group->name = 'Silver Partners';
        $group->code = 'silver';
        $group->save();
    }
}
