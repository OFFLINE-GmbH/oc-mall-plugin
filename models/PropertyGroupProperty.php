<?php

namespace OFFLINE\Mall\Models;

use October\Rain\Database\Pivot;
use October\Rain\Database\Traits\Nullable;
use OFFLINE\Mall\Classes\Traits\SortableRelation;

class PropertyGroupProperty extends Pivot
{
    use Nullable;
    use SortableRelation;

    public $nullable = ['filter_type'];

    public static function getFilterTypeOptions($dashes = true)
    {
        return [
            null    => ($dashes ? '-- ' : '') . trans('offline.mall::lang.properties.filter_types.none'),
            'set'   => trans('offline.mall::lang.properties.filter_types.set'),
            'range' => trans('offline.mall::lang.properties.filter_types.range'),
        ];
    }
}
