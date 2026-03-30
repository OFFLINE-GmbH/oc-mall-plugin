<?php

namespace OFFLINE\Mall\Classes\Traits\Cart;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\User\Auth;
use OFFLINE\Mall\Models\Discount;

trait Discounts
{
    /**
     * Apply a discount to this cart. Limit the number of codes that can be applied
     * to a cart by setting the $discountCodeLimit.$discountCodeLimit defaults to 0
     * where user are allowed to apply unlimited codes.
     *
     * @param Discount $discount
     * @param int $discountCodeLimit
     *
     * @throws ValidationException
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
        $customer = optional(Auth::user())->customer;

        if (optional($customer)->orders) {
            $previousOrderDiscounts = $customer->orders->map(fn ($order) => array_get($order, 'discounts.0.discount.id'));
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
            $discount = Discount::isActive()
                ->where(function ($q) use ($code) {
                    // Legacy: code stored directly on the discount
                    $q->where('code', $code)
                    // New: code stored in a conditions row
                    ->orWhereHas('conditions', fn ($cq) => $cq->where('trigger', 'code')->where('code', $code));
                })
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ValidationException([
                'code' => trans('offline.mall::lang.discounts.validation.not_found'),
            ]);
        }

        // Load pivot data to check existing applied codes for this discount.
        $existing = $this->discounts()->withPivot(['applied_codes'])->find($discount->id);

        if ($existing) {
            // Discount already in cart — this is an additional code for an AND-logic multi-code discount.
            $appliedCodes = json_decode($existing->pivot->applied_codes ?? '[]', true) ?: [];

            if (in_array($code, $appliedCodes)) {
                throw new ValidationException([
                    'code' => trans('offline.mall::lang.discounts.validation.duplicate'),
                ]);
            }

            $this->discounts()->updateExistingPivot($discount->id, [
                'applied_codes' => json_encode(array_merge($appliedCodes, [$code])),
            ]);
            $this->unsetRelation('discounts');

            return;
        }

        // First code for this discount — attach and record the applied code.
        $this->applyDiscount($discount, $discountCodeLimit);

        $this->discounts()->updateExistingPivot($discount->id, [
            'applied_codes' => json_encode([$code]),
        ]);
        $this->unsetRelation('discounts');
    }

    /**
     * Updates the `number_of_usages` property on each
     * applied discount of this cart.
     */
    public function updateDiscountUsageCount()
    {
        $this->totals()->appliedDiscounts()->each(function (array $discount) {
            if (is_array($discount) && array_key_exists('discount', $discount)) {
                $discount = $discount['discount'];
            }
            $discount->number_of_usages++;
            $discount->save();
        });

        if ($shippingDiscount = $this->totals()->shippingTotal()->appliedDiscount()) {
            if (is_array($shippingDiscount) && array_key_exists('discount', $shippingDiscount)) {
                $shippingDiscount = $shippingDiscount['discount'];
            }
            $shippingDiscount->number_of_usages++;
            $shippingDiscount->save();
        }
    }

    /**
     * Removes a specific disount from a cart.
     *
     * @param int $id
     * @throws ValidationException
     */
    public function removeDiscountCodeById(int $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $discount = Discount::find($id);

                $this->discounts()->remove($discount);
                $this->unsetRelation('discounts');

                $code = $discount->getEffectiveCode();

                $this->totals()->appliedDiscounts()->each(function (array $discount) use ($code) {
                    if ($code !== null && $code === $discount['discount']->getEffectiveCode() && $discount['discount']->number_of_usages > 0) {
                        $discount['discount']->number_of_usages--;
                        $discount['discount']->save();
                    }
                });
            });
        } catch (ModelNotFoundException $e) {
            throw new ValidationException([
                'code' => trans('offline.mall::lang.discounts.validation.not_found'),
            ]);
        }
    }
}
