<?php

namespace OFFLINE\Mall\Classes\Auth;

use Throwable;

class BasicAuth
{
    public $username;
    public $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Check a Authorization header against a username and password.
     *
     * @param string $header
     *
     * @return bool|null
     */
    public function check(string $header): ?bool
    {
        try {
            $base64 = str_replace('Bearer ', '', $header);

            [$username, $password] = explode(':', base64_decode($base64));

            return $this->username === $username && $this->password === $password;
        } catch (Throwable $e) {
            return false;
        }
    }
}