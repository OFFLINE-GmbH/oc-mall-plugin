<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Nullable;
use October\Rain\Database\Traits\Validation;

class DiscountCondition extends Model
{
    use Validation;
    use Nullable;

    public const MORPH_KEY = 'mall.discount_condition';

    public $table = 'offline_mall_discount_conditions';

    public $rules = [
        'discount_id' => 'required|integer',
        'trigger'     => 'required|in:total,code,product,customer_group,shipping_method,payment_method',
        'code'        => 'nullable|string',
    ];

    public $fillable = [
        'discount_id',
        'trigger',
        'code',
        'product_id',
        'minimum_quantity',
        'customer_group_id',
        'payment_method_id',
        'minimum_total',
        'shipping_method_ids',
        'sort_order',
    ];

    public $nullable = [
        'code',
        'product_id',
        'minimum_quantity',
        'customer_group_id',
        'payment_method_id',
        'minimum_total',
        'shipping_method_ids',
    ];

    public $casts = [
        'minimum_total'    => 'float',
        'minimum_quantity' => 'integer',
    ];

    public $jsonable = ['shipping_method_ids'];

    public $belongsTo = [
        'discount'       => [Discount::class],
        'product'        => [Product::class],
        'customer_group' => [CustomerGroup::class],
        'payment_method' => [PaymentMethod::class],
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function (self $condition) {
            if ($condition->trigger === 'code' && ! $condition->code) {
                $condition->code = strtoupper(str_random(10));
            }

            $condition->code = strtoupper($condition->code ?? '');

            if ($condition->trigger !== 'product') {
                $condition->product_id       = null;
                $condition->minimum_quantity = null;
            }
            if ($condition->trigger !== 'code') {
                $condition->code = null;
            }
            if ($condition->trigger !== 'customer_group') {
                $condition->customer_group_id = null;
            }
            if ($condition->trigger !== 'payment_method') {
                $condition->payment_method_id = null;
            }
            if ($condition->trigger !== 'total') {
                $condition->minimum_total = null;
            }
            if ($condition->trigger !== 'shipping_method') {
                $condition->shipping_method_ids = null;
            }
        });
    }

    public function getTriggerOptions(): array
    {
        $keys = ['total', 'code', 'product', 'shipping_method', 'customer_group', 'payment_method'];

        return collect($keys)->mapWithKeys(fn ($key) => [$key => trans('offline.mall::lang.discounts.triggers.' . $key)])->toArray();
    }

    public function getProductIdOptions(): array
    {
        return [null => trans('offline.mall::lang.common.none')] + Product::get()->pluck('name', 'id')->toArray();
    }

    public function getCustomerGroupIdOptions(): array
    {
        return [null => trans('offline.mall::lang.common.none')] + CustomerGroup::get()->pluck('name', 'id')->toArray();
    }

    public function getPaymentMethodIdOptions(): array
    {
        return [null => trans('offline.mall::lang.common.none')] + PaymentMethod::get()->pluck('name', 'id')->toArray();
    }

    public function getShippingMethodIdsOptions(): array
    {
        return ShippingMethod::get()->pluck('name', 'id')->toArray();
    }
}
