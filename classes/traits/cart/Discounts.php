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
     * Apply a discount to this cart. Limit the number of codes that can be applied
     * to a cart by setting the $discountCodeLimit.$discountCodeLimit defaults to 0
     * where user are allowed to apply unlimited codes.
     *
     * @param Discount $discount
     * @param int      $discountCodeLimit
     *
     * @throws \October\Rain\Exception\ValidationException
     * @throws ValidationException
     */
    public function applyDiscount(Discount $discount, int $discountCodeLimit = 0)
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

        if ($discountCodeLimit > 0 && $this->discounts->count() >= $discountCodeLimit) {
            throw new ValidationException([trans('offline.mall::lang.discounts.validation.cart_limit_reached')]);
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

    public function applyDiscountByCode(string $code, int $discountCodeLimit)
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

        return $this->applyDiscount($discount, $discountCodeLimit);
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
    
    
    public function removeDiscountCodeById(int $id)
    {
        try {
            $discount = Discount::find($id);
            $code = $discount->code;
            $this->discounts()->remove($discount);

            $this->totals()->appliedDiscounts()->each(function (array $discount) use ($code) {
                if ($code == $discount['discount']->code) {
                    $discount['discount']->number_of_usages--;
                    $discount['discount']->save();
                }
            });

        } catch (ModelNotFoundException $e) {
            throw new ValidationException([
                'code' => trans('offline.mall::lang.discounts.validation.not_found'),
            ]);
        }
    }
}
