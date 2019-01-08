<?php

namespace OFFLINE\Mall\Classes\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\OrderState;
use System\Classes\PluginManager;

class OrderStateTableSeeder extends Seeder
{
    public function run()
    {
        $isTranslatable = PluginManager::instance()->hasPlugin('RainLab.Translate');
        $states         = [
            ['name' => 'New', 'flag' => OrderState::FLAG_NEW, 'color' => '#3498db', 'german_name' => 'Neu'],
            ['name' => 'In Progress', 'color' => '#f1c40f', 'german_name' => 'Wird bearbeitet'],
            ['name' => 'Disputed', 'color' => '#d30000', 'german_name' => 'Reklamiert'],
            [
                'name'        => 'Cancelled',
                'flag'        => OrderState::FLAG_CANCELLED,
                'color'       => '#5e667f',
                'german_name' => 'Storniert',
            ],
            [
                'name'        => 'Complete',
                'flag'        => OrderState::FLAG_COMPLETE,
                'color'       => '#189e51',
                'german_name' => 'Abgeschlossen',
            ],
        ];
        foreach ($states as $state) {
            $s = new OrderState();
            if ($isTranslatable) {
                $s->translateContext('en');
            }

            $s->forceFill(array_except($state, 'german_name'));
            $s->save();

            if ($isTranslatable) {
                $s->translateContext('de');
                $s->name = $state['german_name'];
                $s->save();
            }
        }
    }
}
