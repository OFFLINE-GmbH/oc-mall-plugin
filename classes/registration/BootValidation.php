<?php

namespace OFFLINE\Mall\Classes\Registration;

use OFFLINE\Mall\Models\User as RainLabUser;
use Validator;

trait BootValidation
{
    protected function registerValidationRules()
    {
        Validator::extend('non_existing_user', function ($attribute, $value, $parameters) {
            return RainLabUser
                    ::with('customer')
                    ->where('email', $value)
                    ->whereHas('customer', function ($q) {
                        $q->where('is_guest', 0);
                    })->count() === 0;
        });
    }
}
