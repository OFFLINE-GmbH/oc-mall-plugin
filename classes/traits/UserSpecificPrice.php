<?php

namespace OFFLINE\Mall\Classes\Traits;

use RainLab\User\Facades\Auth;

trait UserSpecificPrice
{
    public function getUserSpecificPrice()
    {
        if ( ! $this->hasUserSpecificPrice()) {
            return null;
        }

        $group = optional(Auth::getUser())->offline_mall_customer_group_id;
        $price = $this->customer_group_prices->where('customer_group_id', $group);

        return $price && $price->count() > 0 ? $price : null;
    }

    protected function hasUserSpecificPrice(): bool
    {
        return ! app()->runningInBackend()
            && app()->has('user.auth')
            && optional(Auth::getUser())->offline_mall_customer_group_id !== null
            && $this->customer_group_prices->count() > 0;
    }

}
