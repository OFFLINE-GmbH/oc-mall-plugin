<?php


namespace OFFLINE\Mall\Classes\Customer;

use RainLab\User\Models\User;

interface SignInHandler
{
    public function handle(array $postData): ?User;
}
