<?php

namespace OFFLINE\Mall\Classes\Registration;

use RainLab\User\Models\User;
use Validator;

trait BootValidation
{
    protected function registerValidationRules()
    {
        Validator::extend('non_existing_user', function ($attribute, $value, $parameters) {
            return User::with('customer')
                    ->where('email', $value)
                    ->whereHas('customer', function ($q) {
                        $q->where('is_guest', 0);
                    })
                    ->count() === 0;
        });
    }
}
