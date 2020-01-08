<?php

namespace OFFLINE\Mall\Classes\Registration;

use OFFLINE\Mall\Classes\Utils\Money;

trait BootTwig
{
    public function registerMarkupTags()
    {
        $filters = [
            'money' => function (...$args) {
                return app(Money::class)->format(...$args);
            },
        ];

        // Check the translate plugin is installed
        if ( ! class_exists('RainLab\Translate\Behaviors\TranslatableModel')) {
            $filters['_']  = ['Lang', 'get'];
            $filters['__'] = ['Lang', 'choice'];
        }

        return [
            'filters' => $filters,
        ];
    }
}
