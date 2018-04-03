<?php

namespace OFFLINE\Mall\Models;

use RainLab\User\Models\User as UserBase;

class User extends UserBase
{
    public $rules = [
        'email'                 => 'required|between:6,255|email',
        'avatar'                => 'nullable|image|max:4000',
        'password'              => 'required:create|between:4,255|confirmed',
        'password_confirmation' => 'required_with:password|between:4,255',
    ];
}
