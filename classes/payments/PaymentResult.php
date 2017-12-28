<?php

namespace OFFLINE\Mall\Classes\Payments;


use OFFLINE\Mall\Models\FailedPayment;

class PaymentResult
{
    /**
     * @var bool
     */
    public $successful = false;
    /**
     * @var FailedPayment
     */
    public $failedPayment;
}