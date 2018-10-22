<?php namespace OFFLINE\Mall\Models;

use October\Rain\Database\Traits\Nullable;
use October\Rain\Database\Traits\Validation;

class ProductPrice extends Price
{
    use Validation;
    use Nullable;

    public $table = 'offline_mall_product_prices';
    public $nullable = ['price'];
    // Remove parent relation
    public $morphTo = [
    ];
    public $fillable = [
        'price',
        'currency_id',
        'customer_group_id',
        'product_id',
        'variant_id',
    ];
    public $rules = [
    ];
    public $belongsTo = [
        'product'  => Product::class,
        'variant'  => Variant::class,
        'currency' => Currency::class,
    ];
}
