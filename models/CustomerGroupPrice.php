<?php namespace OFFLINE\Mall\Models;

use Model;
use OFFLINE\Mall\Classes\Traits\Price;

class CustomerGroupPrice extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Price;

    public $rules = [
        'price' => 'required',
    ];
    public $jsonable = ['price'];
    public $table = 'offline_mall_customer_group_prices';
    public $morphTo = [
        'priceable' => [],
    ];
    public $fillable = [
        'customer_group_id',
        'priceable_id',
        'priceable_type',
        'price',
    ];
}
