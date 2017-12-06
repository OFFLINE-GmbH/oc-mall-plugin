<?php namespace OFFLINE\Mall\Models;

use Model;

class Discount extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $rules = [
        'name'                                 => 'required',
        'expires'                              => 'date',
        'max_number_of_usages'                 => 'numeric',
        'trigger'                              => 'in:total,code,product',
        'types'                                => 'in:fixed_amount,rate,alternate_price,shipping',
        'code'                                 => 'required_if:trigger,code',
        'total_to_reach'                       => 'required_if:trigger,total|numeric',
        'product'                              => 'required_if:trigger,product',
        'type'                                 => 'in:fixed_amount,rate,alternate_price,shipping',
        'amount'                               => 'required_if:type,fixed_amount|numeric',
        'rate'                                 => 'required_if:type,rate|numeric',
        'alternate_price'                      => 'required_if:type,alternate_price|numeric',
        'shipping_description'                 => 'required_if:type,shipping',
        'shipping_cost'                        => 'required_if:type,shipping|numeric',
        'shipping_guaranteed_days_to_delivery' => 'numeric',
    ];

    public $table = 'offline_mall_discounts';

    public $belongsTo = [
        'product' => [Product::class],
    ];

    public $belongsToMany = [
        'carts' => [Cart::class],
    ];

    public function getTypeOptions()
    {
        return trans('offline.mall::lang.discounts.types');
    }

    public function getTriggerOptions()
    {
        return trans('offline.mall::lang.discounts.triggers');
    }

    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = (float)$value * 100;
    }

    public function getAmountAttribute($value)
    {
        return $this->formatPrice((int)$value / 100);
    }

    public function setAlternatePriceAttribute($value)
    {
        $this->attributes['alternate_price'] = (float)$value * 100;
    }

    public function getAlternatePriceAttribute($value)
    {
        return $this->formatPrice((int)$value / 100);
    }

    public function setShippingCostAttribute($value)
    {
        $this->attributes['shipping_cost'] = (float)$value * 100;
    }

    public function getShippingCostAttribute($value)
    {
        return $this->formatPrice((int)$value / 100);
    }

    public function formatPrice($price)
    {
        return number_format($price, 2, '.', '');
    }
}
