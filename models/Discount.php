<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Carbon\Carbon;
use Event;
use Model;
use October\Rain\Database\Traits\Nullable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\PriceAccessors;

class Discount extends Model
{
    use Validation;
    use PriceAccessors;
    use Nullable;
    use HashIds;

    public const MORPH_KEY = 'mall.discount';

    public $rules = [
        'name'                                 => 'required',
        'valid_from'                           => 'nullable|date',
        'expires'                              => 'nullable|date',
        'number_of_usages'                     => 'nullable|numeric',
        'max_number_of_usages'                 => 'nullable|numeric',
        'code'                                 => 'nullable|unique:offline_mall_discounts,code',
        'type'                                 => 'in:fixed_amount,rate,shipping',
        'rate'                                 => 'required_if:type,rate|nullable|numeric',
        'shipping_description'                 => 'required_if:type,shipping',
        'shipping_guaranteed_days_to_delivery' => 'nullable|numeric',
    ];

    public $with = ['shipping_prices', 'amounts', 'totals_to_reach', 'conditions'];

    public $table = 'offline_mall_discounts';

    public $dates = ['valid_from', 'expires'];

    public $nullable = ['max_number_of_usages'];

    public $casts = [
        'number_of_usages' => 'integer',
    ];

    public $morphMany = [
        'shipping_prices' => [
            Price::class,
            'name' => 'priceable',
            'conditions' => "field = 'shipping_prices'",
        ],
        'amounts' => [
            Price::class,
            'name' => 'priceable',
            'conditions' => "field = 'amounts'",
        ],
        'totals_to_reach' => [
            Price::class,
            'name' => 'priceable',
            'conditions' => "field = 'totals_to_reach'",
        ],
    ];

    public $fillable = [
        'name',
        'valid_from',
        'expires',
        'number_of_usages',
        'max_number_of_usages',
        'conditions_operator',
        'type',
        'rate',
        'shipping_description',
        'shipping_guaranteed_days_to_delivery',
    ];

    public $hasMany = [
        'conditions' => [DiscountCondition::class],
    ];

    public $belongsToMany = [
        'carts'            => [Cart::class, 'table' => 'offline_mall_cart_discount'],
        'shipping_methods' => [ShippingMethod::class, 'table' => 'offline_mall_shipping_method_discount'],
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
            $discount->code = strtoupper($discount->code ?? '');
        });
    }

    public function scopeIsActive($q)
    {
        $q->where(function ($q) {
            $q->where(function ($q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', Carbon::now());
            })->where(function ($q) {
                $q->whereNull('expires')->orWhere('expires', '>', Carbon::now());
            });
        });
    }

    public function getTypeOptions()
    {
        $keys = ['fixed_amount', 'rate', 'shipping'];
        $options = collect($keys)->mapWithKeys(fn ($key) => [$key => trans('offline.mall::lang.discounts.types.' . $key)]);

        Event::fire('mall.discount.extendTypeOptions', [&$options]);

        return $options;
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

    public function getConditionsOperatorOptions(): array
    {
        return [
            'and' => trans('offline.mall::lang.discounts.conditions_operator_and'),
            'or'  => trans('offline.mall::lang.discounts.conditions_operator_or'),
        ];
    }

    public function getConditionItemsAttribute(): array
    {
        return $this->conditions->map(fn (DiscountCondition $c) => [
            'id'                  => $c->id,
            'trigger'             => $c->trigger,
            'code'                => $c->code,
            'product_id'          => $c->product_id,
            'minimum_quantity'    => $c->minimum_quantity,
            'customer_group_id'   => $c->customer_group_id,
            'payment_method_id'   => $c->payment_method_id,
            'minimum_total'       => $c->minimum_total,
            'shipping_method_ids' => $c->shipping_method_ids,
            'sort_order'          => $c->sort_order,
        ])->values()->toArray();
    }

    public function getAppliedCodesFromPivotAttribute(): array
    {
        return json_decode($this->pivot?->applied_codes ?? '[]', true) ?: [];
    }

    public function getEffectiveCode(): ?string
    {
        $codeCondition = $this->conditions->firstWhere('trigger', 'code');
        if ($codeCondition) {
            return $codeCondition->code;
        }

        return $this->code ?: null;
    }
}
