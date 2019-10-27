<?php

namespace OFFLINE\Mall\Classes\Index\MySQL;


use Model;

class IndexEntry extends Model
{
    public $table = 'offline_mall_index';
    public $guarded = ['id'];
    public $timestamps = false;

    public $casts = [
        'category_id'           => 'array',
        'property_values'       => 'array',
        'sort_orders'           => 'array',
        'prices'                => 'array',
        'parent_prices'         => 'array',
        'customer_group_prices' => 'array',
    ];
}
