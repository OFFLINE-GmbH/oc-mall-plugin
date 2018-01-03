<?php namespace OFFLINE\Mall\Models;

use Model;
use OFFLINE\Mall\Classes\Traits\Price;

/**
 * Model
 */
class Variant extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;
    use Price;

    protected $dates = ['deleted_at'];
    public $casts = [
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
    ];

    public $rules = [
        'name'                         => 'required',
        'product_id'                   => 'required|exists:offline_mall_products,id',
        'stock'                        => 'integer',
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
        'price'                        => 'required|nullable|regex:/\d+([\.,]\d+)?/i',
        'old_price'                    => 'required|nullable|regex:/\d+([\.,]\d+)?/i',
    ];

    public $table = 'offline_mall_product_variants';

    public $belongsTo = [
        'product'      => Product::class,
        'cart_product' => CartProduct::class,
    ];

    public $hasMany = [
        'property_values' => PropertyValue::class,
    ];

    public function getPriceColumns()
    {
        return ['price', 'old_price'];
    }
}
