<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\PriceAccessors;

class ShippingMethodRate extends Model
{
    use Validation;
    use PriceAccessors;

    const MORPH_KEY = 'mall.shipping_method_rate';

    public $timestamps = false;
    public $with = ['prices'];
    public $morphMany = [
        'prices' => [
            Price::class,
            'name'       => 'priceable',
            'conditions' => 'price_category_id is null and field is null',
        ],
    ];
    public $rules = [
        'from_weight' => 'integer|min:0',
        'to_weight'   => 'min:0',
    ];
    public $casts = [
        'from_weight' => 'int',
        'to_weight'   => 'int',
    ];
    public $table = 'offline_mall_shipping_method_rates';
    public $belongsTo = [
        'shipping_method' => ShippingMethod::class,
    ];
}
