<?php


namespace OFFLINE\Mall\Classes\Customer;

use OFFLINE\Mall\Models\User;

interface SignInHandler
{
    public function handle(array $postData): ?User;
}
