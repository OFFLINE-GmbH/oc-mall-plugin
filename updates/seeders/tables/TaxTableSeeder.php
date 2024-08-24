<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Tax;

class TaxTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @param bool $useDemo
     * @return void
     */
    public function run(bool $useDemo = false)
    {
        if ($useDemo) {
            return;
        }
        
        $country = 'de';

        if ($country == 'de') {
            Tax::create([
                'name'          => trans('offline.mall::demo.taxes.standard'),
                'percentage'    => 19,
                'is_default'    => true,
            ]);
    
            Tax::create([
                'name'          => trans('offline.mall::demo.taxes.reduced'),
                'percentage'    => 7,
            ]);
        } elseif ($country == 'at') {
            Tax::create([
                'name'          => trans('offline.mall::demo.taxes.standard'),
                'percentage'    => 20,
                'is_default'    => true,
            ]);
    
            Tax::create([
                'name'          => trans('offline.mall::demo.taxes.reduced'),
                'percentage'    => 10,
            ]);
        } elseif ($country == 'ch') {
            Tax::create([
                'name'          => trans('offline.mall::demo.taxes.standard'),
                'percentage'    => 8.1,
                'is_default'    => true,
            ]);
    
            Tax::create([
                'name'          => trans('offline.mall::demo.taxes.reduced'),
                'percentage'    => 2.6,
            ]);
        }
    }
}
