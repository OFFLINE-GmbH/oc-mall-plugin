<?php

namespace OFFLINE\Mall\Classes\User;

/**
 * Auth forwards calls to the RainLab\User\Facades\Auth if available (< 3.0)
 * or the default Laravel Auth facade (>= 3.0)
 */
class Auth
{
    public static function __callStatic($method, $args)
    {
        if (class_exists(\RainLab\User\Facades\Auth::class)) {
            return call_user_func_array([\RainLab\User\Facades\Auth::class, $method], $args);
        }

        return call_user_func_array([\Illuminate\Support\Facades\Auth::class, $method], $args);
    }
}
