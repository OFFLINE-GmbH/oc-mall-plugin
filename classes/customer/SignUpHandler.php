<?php


namespace OFFLINE\Mall\Classes\Customer;

use OFFLINE\Mall\Models\User;

interface SignUpHandler
{
    public function handle(array $postData, bool $asGuest = false): ?User;
}
