<?php

namespace OFFLINE\Mall\Classes\Registration;

use OFFLINE\Mall\Classes\Validation\NonExistingUserRule;

trait BootValidation
{
    protected function registerValidationRules()
    {
        $this->registerValidationRule('non_existing_user', NonExistingUserRule::class);
    }
}
