<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\Price;

class ShippingMethodRate extends Model
{
    use Validation;
    use Price;

    public $timestamps = false;
    public $rules = [
        'price'       => 'required|regex:/\d+([\.,]\d+)?/i',
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
