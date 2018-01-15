<?php

namespace OFFLINE\Mall\Classes\PaymentStatus;

use OFFLINE\Mall\Models\Order;

abstract class PaymentStatus
{
    private $order;

    abstract public function getAvailableTransitions() : array;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
