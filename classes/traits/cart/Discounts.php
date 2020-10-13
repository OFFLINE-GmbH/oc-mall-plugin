<?php

namespace OFFLINE\Mall\Classes\Traits\Cart;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Models\Discount;
use RainLab\User\Facades\Auth;

trait Discounts
{
    /**
     * Apply a discount to this cart.
     *
     * @param Discount $discount
     *
     * @throws \October\Rain\Exception\ValidationException
     * @throws ValidationException
     */
    public function applyDiscount(Discount $discount)
    {
        $uniqueDiscountTypes = ['shipping'];

        if (in_array($discount->type, $uniqueDiscountTypes)
            && $this->discounts->where('type', $discount->type)->count() > 0) {
            throw new ValidationException([trans('offline.mall::lang.discounts.validation.' . $discount->type)]);
        }

        $previousOrderDiscounts = collect();
        $customer = optional(Auth::getUser())->customer;
        if (optional($customer)->orders) {
            $previousOrderDiscounts = $customer->orders->map(function ($order) {
                return array_get($order, 'discounts.0.discount.id');
            });
        }

        if ($this->discounts->contains($discount) || $previousOrderDiscounts->contains($discount->id)) {
            throw new ValidationException([trans('offline.mall::lang.discounts.validation.duplicate')]);
        }

        if ($discount->valid_from && $discount->valid_from->gte(Carbon::now())) {
            throw new ValidationException([trans('offline.mall::lang.discounts.validation.not_found')]);
        }

        if ($discount->expires && $discount->expires->lt(Carbon::today())) {
            throw new ValidationException([trans('offline.mall::lang.discounts.validation.expired')]);
        }

        if ($discount->max_number_of_usages !== null && $discount->number_of_usages >= $discount->max_number_of_usages) {
            throw new ValidationException([trans('offline.mall::lang.discounts.validation.usage_limit_reached')]);
        }

        $this->discounts()->save($discount);
    }

    public function applyDiscountByCode(string $code)
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            throw new ValidationException([
                'code' => trans('offline.mall::lang.discounts.validation.empty'),
            ]);
        }

        try {
            $discount = Discount::isActive()->whereCode($code)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ValidationException([
                'code' => trans('offline.mall::lang.discounts.validation.not_found'),
            ]);
        }

        return $this->applyDiscount($discount);
    }

    /**
     * Updates the `number_of_usages` property on each
     * applied discount of this cart.
     */
    public function updateDiscountUsageCount()
    {
        $this->totals()->appliedDiscounts()->each(function (array $discount) {
            $discount['discount']->number_of_usages++;
            $discount['discount']->save();
        });

        if ($shippingDiscount = $this->totals()->shippingTotal()->appliedDiscount()) {
            $shippingDiscount['discount']->number_of_usages++;
            $shippingDiscount['discount']->save();
        }
    }
}
