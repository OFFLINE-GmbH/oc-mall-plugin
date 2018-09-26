<?php namespace OFFLINE\Mall\Models;

class CustomerGroupPrice extends Price
{
    use \October\Rain\Database\Traits\Validation;

    public $rules = [
        'price' => 'required',
    ];
    public $table = 'offline_mall_customer_group_prices';
    public $morphTo = [
        'priceable' => [],
    ];
    public $fillable = [
        'customer_group_id',
        'currency_id',
        'priceable_id',
        'priceable_type',
        'price',
    ];
}
