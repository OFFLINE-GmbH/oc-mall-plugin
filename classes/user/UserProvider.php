<?php

namespace OFFLINE\Mall\Classes\User;

use Illuminate\Auth\EloquentUserProvider;

class UserProvider extends \RainLab\User\Classes\UserProvider
{
    /**
     * RainLab.User restricts logins to registered users only (starting from 3.0).
     * We handle this logic separately, so we can allow any user to login.
     * @param null|mixed $model
     */
    protected function newModelQuery($model = null)
    {
        return EloquentUserProvider::newModelQuery($model);
    }
}
