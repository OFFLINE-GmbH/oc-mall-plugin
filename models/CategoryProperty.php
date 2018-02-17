<?php

namespace OFFLINe\Mall\Models;

use October\Rain\Database\Pivot;
use October\Rain\Database\Traits\Nullable;

class CategoryProperty extends Pivot
{
    use Nullable;

    public $nullable = ['filter_type'];

    public function getFilterTypeOptions()
    {
        return [
            null    => '-- ' . trans('offline.mall::lang.properties.filter_types.none'),
            'set'   => trans('offline.mall::lang.properties.filter_types.set'),
            'range' => trans('offline.mall::lang.properties.filter_types.range'),
        ];
    }
}
