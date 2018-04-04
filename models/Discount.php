<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\Price;

class Discount extends Model
{
    use Validation;
    use Price;

    public $rules = [
        'name'                                 => 'required',
        'expires'                              => 'nullable|date',
        'number_of_usages'                     => 'nullable|numeric',
        'max_number_of_usages'                 => 'nullable|numeric',
        'trigger'                              => 'in:total,code,product',
        'types'                                => 'in:fixed_amount,rate,alternate_price,shipping',
        'code'                                 => 'required_if:trigger,code',
        'total_to_reach'                       => 'required_if:trigger,total|nullable|numeric',
        'product'                              => 'required_if:trigger,product',
        'type'                                 => 'in:fixed_amount,rate,alternate_price,shipping',
        'amount'                               => 'required_if:type,fixed_amount|nullable|numeric',
        'rate'                                 => 'required_if:type,rate|nullable|numeric',
        'alternate_price'                      => 'required_if:type,alternate_price|nullable|numeric',
        'shipping_description'                 => 'required_if:type,shipping',
        'shipping_price'                       => 'required_if:type,shipping|nullable|numeric',
        'shipping_guaranteed_days_to_delivery' => 'nullable|numeric',
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

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = [
        'name',
        'shipping_description',
    ];

    public static function boot()
    {
        parent::boot();
        static::saving(function (self $discount) {
            $discount->code = strtoupper($discount->code);
            if ($discount->trigger !== 'product') {
                $discount->product_id = null;
            }
        });
    }

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

    public function getProductIdOptions()
    {
        return collect([null => '--'])->merge(Product::get()->pluck('name', 'id'));
    }
}
