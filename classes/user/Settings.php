<?php

namespace OFFLINE\Mall\Classes\User;

use October\Rain\Support\Facades\Config;

/**
 * Settings forwards calls to the RainLab\User\Models\Settings if available (< 3.0)
 * or the new RainLab\User\Models\Setting (>= 3.0)
 */
class Settings
{
    public const ACTIVATE_USER = 'user';

    public static function __callStatic($method, $args)
    {
        if (class_exists(\RainLab\User\Models\Settings::class)) {
            return call_user_func_array([\RainLab\User\Models\Settings::class, $method], $args);
        }

        return call_user_func_array([\RainLab\User\Models\Setting::class, $method], $args);
    }

    public static function getMinPasswordLength()
    {
        return Config::get('rainlab.user::minPasswordLength', Config::get('rainlab.user::password_policy.min_length', 8));
    }
}
