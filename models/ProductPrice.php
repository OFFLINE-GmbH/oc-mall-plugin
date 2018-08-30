<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Nullable;
use October\Rain\Database\Traits\Validation;

class ProductPrice extends Model
{
    use Validation;
    use Nullable;

    public $table = 'offline_mall_product_prices';
    public $nullable = ['price'];
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

    public function setPriceAttribute($value)
    {
        if ($value === null) {
            return $this->attributes['price'] = null;
        }
        $this->attributes['price'] = (int)$value * 100;
    }

    public function getFloatAttribute()
    {
        if ($this->price === null) {
            return null;
        }

        return (float)$this->price / 100;
    }

    public function getDecimalAttribute()
    {
        if ($this->price === null) {
            return null;
        }

        return number_format($this->price / 100, 2, '.', '');
    }

    public function getIntegerAttribute()
    {
        if ($this->price === null) {
            return null;
        }

        return (int)$this->price;
    }
}
