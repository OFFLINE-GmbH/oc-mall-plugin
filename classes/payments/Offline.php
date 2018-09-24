<?php

namespace OFFLINE\Mall\Classes\Payments;

class Offline extends PaymentProvider
{
    public function name(): string
    {
        return 'Offline';
    }

    public function identifier(): string
    {
        return 'offline';
    }

    public function validate(): bool
    {
        return true;
    }

    public function process(): PaymentResult
    {
        $result = new PaymentResult();
        $result->successful    = true;
        return $result;
    }

    public function settings(): array
    {
        return [];
    }

    public function encryptedSettings(): array
    {
        return [];
    }
}
