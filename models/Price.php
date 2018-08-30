<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Nullable;

class Price extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Nullable;

    public $nullable = ['price'];
    public $rules = [
    ];
    public $table = 'offline_mall_prices';
    public $morphTo = [
        'priceable' => [],
    ];
    public $fillable = [
        'currency_id',
        'price_category_id',
        'priceable_id',
        'priceable_type',
        'price',
        'field',
    ];
    public $belongsTo = [
        'category' => [PriceCategory::class],
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
