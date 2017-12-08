<?php namespace OFFLINE\Mall\Models;

use Model;
use OFFLINE\Mall\Classes\Traits\Price;

/**
 * Model
 */
class Order extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;
    use Price;

    protected $dates = ['deleted_at'];

    public $rules = [
        'currency'                         => 'required',
        'shipping_address_same_as_billing' => 'required|boolean',
        'billing_address'                  => 'required',
        'shipping'                         => 'required',
        'taxes'                            => 'required',
        'discounts'                        => 'required',
        'lang'                             => 'required',
        'ip_address'                       => 'required',
        'user_id'                          => 'required|exists:users,id',
    ];

    public $jsonable = ['billing_address', 'shipping_address', 'custom_fields', 'taxes', 'discounts', 'shipping'];

    public $table = 'offline_mall_orders';

    public $hasMany = [
        'products' => OrderProduct::class,
    ];

    public function getPriceColumns(): array
    {
        return [
            'total_pre_taxes',
            'total_post_taxes',
            'total_product',
            'total_taxes',
            'total_shipping',
        ];
    }
}
