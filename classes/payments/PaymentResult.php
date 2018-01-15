<?php

namespace OFFLINE\Mall\Classes\Payments;

use OFFLINE\Mall\Models\PaymentLog;
use Omnipay\Common\Message\RedirectResponseInterface;

class PaymentResult
{
    /**
     * @var bool
     */
    public $successful = false;
    /**
     * @var bool
     */
    public $redirect = false;
    /**
     * @var RedirectResponseInterface
     */
    public $redirectResponse = null;
    /**
     * @var string
     */
    public $redirectUrl = '';
    /**
     * @var PaymentLog
     */
    public $failedPayment;
}
