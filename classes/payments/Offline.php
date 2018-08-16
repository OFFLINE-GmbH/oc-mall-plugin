<?php

namespace OFFLINE\Mall\Classes\Payments;

use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\PaymentState\FailedState;
use OFFLINE\Mall\Classes\PaymentState\PaidState;
use OFFLINE\Mall\Models\PaymentGatewaySettings;
use Omnipay\Omnipay;
use Throwable;
use Validator;

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
