<?php

namespace OFFLINE\Mall\Classes\Traits\Cart;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Models\Discount;

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

        if ($this->discounts->contains($discount)) {
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
        
        // check whether this user already applied the same code before and deny it if so
        $user = \Auth::getUser();
        $customer = $user->customer;
        $orders = \OFFLINE\Mall\Models\Order
                        ::where('customer_id', $customer->id)
                        ->get(); // there might be away to search here directly for the promo code to avoid the following loop?
        $collection = new \Illuminate\Database\Eloquent\Collection;
        $collection = $orders->filter(function($order) use ($discount) {
            if (! empty($order->discounts)) {
                $discounts = $order->discounts;
                foreach ($discounts as $appliedDiscount) {
                    $appliedDiscount = $appliedDiscount['discount'];
                    if ($appliedDiscount['trigger'] == 'code'
                        && $discount->code == $appliedDiscount['code']) {
                        return true;
                    }
                }
            }
            return false;
        });

        if ($collection->count() > 0) {
            throw new ValidationException([trans('offline.mall::lang.discounts.validation.expired') . ' You already used this code in a previous order.' ]);
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
