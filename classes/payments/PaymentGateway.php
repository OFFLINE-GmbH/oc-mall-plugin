<?php


namespace OFFLINE\Mall\Classes\Payments;


use OFFLINE\Mall\Models\Order;

interface PaymentGateway
{
    public function register(PaymentMethod $method);

    public function process(Order $order, array $data): PaymentResult;
}