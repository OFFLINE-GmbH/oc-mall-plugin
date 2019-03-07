<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Nullable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\PriceAccessors;

class Discount extends Model
{
    use Validation;
    use PriceAccessors;
    use Nullable;

    const MORPH_KEY = 'mall.discount';

    public $rules = [
        'name'                                 => 'required',
        'expires'                              => 'nullable|date',
        'number_of_usages'                     => 'nullable|numeric',
        'max_number_of_usages'                 => 'nullable|numeric',
        'trigger'                              => 'in:total,code,product',
        'types'                                => 'in:fixed_amount,rate,shipping',
        'code'                                 => 'required_if:trigger,code',
        'product'                              => 'required_if:trigger,product',
        'type'                                 => 'in:fixed_amount,rate,shipping',
        'rate'                                 => 'required_if:type,rate|nullable|numeric',
        'shipping_description'                 => 'required_if:type,shipping',
        'shipping_guaranteed_days_to_delivery' => 'nullable|numeric',
    ];
    public $with = ['shipping_prices', 'amounts', 'totals_to_reach'];
    public $table = 'offline_mall_discounts';
    public $dates = ['expires'];
    public $nullable = ['max_number_of_usages'];
    public $casts = [
        'number_of_usages'     => 'integer',
    ];
    public $morphMany = [
        'shipping_prices'  => [Price::class, 'name' => 'priceable', 'conditions' => 'field = "shipping_price"'],
        'amounts'          => [Price::class, 'name' => 'priceable', 'conditions' => 'field = "amount"'],
        'totals_to_reach'  => [Price::class, 'name' => 'priceable', 'conditions' => 'field = "total_to_reach"'],
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

    public function amount($currency = null)
    {
        return $this->price($currency, 'amounts');
    }

    public function totalToReach($currency = null)
    {
        return $this->price($currency, 'totals_to_reach');
    }

    public function shippingPrice($currency = null)
    {
        return $this->price($currency, 'shipping_prices');
    }

    public function getProductIdOptions()
    {
        return [null => trans('offline.mall::lang.common.none')] + Product::get()->pluck('name', 'id')->toArray();
    }
}
