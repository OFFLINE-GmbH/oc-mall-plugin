<?php

namespace OFFLINE\Mall\Classes\Validation;

use RainLab\User\Models\User;

class NonExistingUserRule
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        return User::with('customer')
            ->where('email', $value)
            ->whereHas('customer', function ($q) {
                $q->where('is_guest', 0);
            })
            ->count() === 0;
    }
}
