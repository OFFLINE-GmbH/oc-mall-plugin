<?php

namespace OFFLINE\Mall\Classes\OrderStatus;

use OFFLINE\Mall\Models\Order;

abstract class OrderStatus
{
    private $order;

    abstract public function getAvailableTransitions() : array;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
