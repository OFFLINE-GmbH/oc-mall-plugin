<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\OrderState;

class OrderStateTableSeeder extends Seeder
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
        
        OrderState::create([
            'name'  => trans('offline.mall::demo.order_states.new'),
            'flag'  => OrderState::FLAG_NEW,
            'color' => '#3498db',
        ]);
        
        OrderState::create([
            'name'  => trans('offline.mall::demo.order_states.in_progress'),
            'color' => '#f1c40f',
        ]);
        
        OrderState::create([
            'name'  => trans('offline.mall::demo.order_states.disputed'),
            'color' => '#d30000',
        ]);
        
        OrderState::create([
            'name'  => trans('offline.mall::demo.order_states.cancelled'),
            'flag'  => OrderState::FLAG_CANCELLED,
            'color' => '#5e667f',
        ]);
        
        OrderState::create([
            'name'  => trans('offline.mall::demo.order_states.complete'),
            'flag'  => OrderState::FLAG_COMPLETE,
            'color' => '#189e51',
        ]);
    }
}
