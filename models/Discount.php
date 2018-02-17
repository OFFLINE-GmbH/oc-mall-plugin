<?php namespace OFFLINE\Mall\Models;

use Model;
use OFFLINE\Mall\Classes\Traits\Price;

class Discount extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Price;

    public $rules = [
        'name'                                 => 'required',
        'expires'                              => 'date',
        'number_of_usages'                     => 'numeric',
        'max_number_of_usages'                 => 'numeric',
        'trigger'                              => 'in:total,code,product',
        'types'                                => 'in:fixed_amount,rate,alternate_price,shipping',
        'code'                                 => 'required_if:trigger,code',
        'total_to_reach'                       => 'required_if:trigger,total|numeric',
        'product'                              => 'required_if:trigger,product',
        'type'                                 => 'in:fixed_amount,rate,alternate_price,shipping',
        'amount'                               => 'required_if:type,fixed_amount|numeric',
        'rate'                                 => 'required_if:type,rate|numeric',
        'alternate_price'                      => 'required_if:type,alternate_price|numeric',
        'shipping_description'                 => 'required_if:type,shipping',
        'shipping_price'                       => 'required_if:type,shipping|numeric',
        'shipping_guaranteed_days_to_delivery' => 'numeric',
    ];

    public $table = 'offline_mall_discounts';

    public $dates = ['expires'];
    public $casts = [
        'number_of_usages'     => 'integer',
        'max_number_of_usages' => 'integer',
    ];

    public $belongsTo = [
        'product' => [Product::class],
    ];

    public $belongsToMany = [
        'carts' => [Cart::class],
    ];

    public function getTypeOptions()
    {
        return trans('offline.mall::lang.discounts.types');
    }

    public function getTriggerOptions()
    {
        return trans('offline.mall::lang.discounts.triggers');
    }

    public function getPriceColumns()
    {
        return ['amount', 'alternate_price', 'shipping_price', 'total_to_reach'];
    }
}
