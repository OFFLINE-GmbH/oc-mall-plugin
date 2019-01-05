<?php

namespace OFFLINE\Mall\Classes\Traits;

use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\Price;
use RainLab\User\Facades\Auth;

trait UserSpecificPrice
{
    public function getUserSpecificPrice(Price $original)
    {
        $group = optional(Auth::getUser())->customer_group;
        if ( ! $this->hasUserSpecificPrice()) {
            return $this->checkDiscount($group, $original);
        }

        $price = $this->customer_group_prices->where('customer_group_id', $group->id);

        // A specific price is set. Use it!
        if ($price && $price->count() > 0) {
            return $price;
        }

        return $this->checkDiscount($group, $original);
    }

    /**
     * Check if the CustomerGroup has a global discount. If so, reduce the price.
     *
     * @param CustomerGroup|null $group
     * @param Price              $original
     *
     * @return Price|null
     */
    protected function checkDiscount(?CustomerGroup $group, Price $original): ?Price
    {
        if ( ! $group || $original->price === null || app()->runningInBackend()) {
            return null;
        }

        // If the customer group has a global discount, apply it to the original price.
        if ($group->discount !== null) {
            return $original->withDiscountPercentage($group->discount);
        }

        return null;
    }

    protected function hasUserSpecificPrice(): bool
    {
        return ! app()->runningInBackend()
            && app()->has('user.auth')
            && optional(Auth::getUser())->offline_mall_customer_group_id !== null
            && $this->customer_group_prices->count() > 0;
    }
}
