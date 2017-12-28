<?php

namespace OFFLINE\Mall\Classes\Payments;


use OFFLINE\Mall\Models\Order;

abstract class PaymentMethod
{
    /**
     * @var Order
     */
    public $order;
    /**
     * @var array
     */
    public $data;

    public function __construct(Order $order = null, array $data = [])
    {
        $this->order = $order;
        $this->data  = $data;
    }

    abstract public static function name(): string;

    abstract public static function identifier(): string;

    abstract public function process(): PaymentResult;

    abstract public function validate(): bool;

    public function setOrder(Order $order)
    {
        $this->order = $order;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function returnUrl(): string
    {
        return '/done';
    }


    public function cancelUrl(): string
    {
        return '/cancel';
    }

}