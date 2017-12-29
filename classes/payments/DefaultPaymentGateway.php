<?php

namespace OFFLINE\Mall\Classes\Payments;


use OFFLINE\Mall\Models\Order;
use Session;

class DefaultPaymentGateway implements PaymentGateway
{
    public $methods = [];

    public function register(PaymentMethod $method)
    {
        $this->methods[$method->identifier()] = get_class($method);
    }

    public function process(Order $order, array $data): PaymentResult
    {
        $method = $this->getMethod($order);
        $method->setOrder($order);
        $method->setData($data);

        $method->validate();

        Session::put('oc-mall.payment.id', str_random(8));

        return $method->process();
    }

    protected function getMethod(Order $order): PaymentMethod
    {
        if (isset($this->methods[$order->payment_method])) {
            return new $this->methods[$order->payment_method];
        }

        throw new \LogicException(
            sprintf('The selected payment method "%s" is unavailable.', $order->payment_method)
        );
    }

}