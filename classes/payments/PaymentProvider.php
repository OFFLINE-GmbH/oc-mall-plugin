<?php

namespace OFFLINE\Mall\Classes\Payments;


use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentLog;
use Request;
use Session;
use Url;

abstract class PaymentProvider
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
        if ($order) {
            $this->setOrder($order);
        }
        if ($data) {
            $this->setData($data);
        }
    }

    abstract public function name(): string;

    abstract public function identifier(): string;

    abstract public function process();

    abstract public function validate(): bool;

    public function setOrder(?Order $order)
    {
        $this->order = $order;
        Session::put('oc-mall.payment.order', optional($this->order)->id);
    }

    public function setData(array $data)
    {
        $this->data = $data;
        Session::put('oc-mall.payment.data', $data);
    }

    protected function getOrderFromSession(): Order
    {
        $id = Session::pull('oc-mall.payment.order');

        return Order::findOrFail($id);
    }

    public function returnUrl(): string
    {
        return Request::url() . '?' . http_build_query([
                'return'             => 'return',
                'oc-mall_payment_id' => $this->getPaymentId(),
            ]);
    }

    public function cancelUrl(): string
    {
        return Request::url() . '?' . http_build_query([
                'return'             => 'cancel',
                'oc-mall_payment_id' => $this->getPaymentId(),
            ]);
    }

    private function getPaymentId()
    {
        return Session::get('oc-mall.payment.id');
    }

    protected function logFailedPayment(array $data = [], $response): PaymentLog
    {
        return $this->logPayment(true, $data, $response);
    }

    protected function logSuccessfulPayment(array $data = [], $response): PaymentLog
    {
        return $this->logPayment(false, $data, $response);
    }

    protected function logPayment(bool $failed, array $data = [], $response): PaymentLog
    {
        $log                 = new PaymentLog();
        $log->failed         = $failed;
        $log->ip             = request()->ip();
        $log->session_id     = session()->get('cart_session_id');
        $log->data           = $data;
        $log->payment_method = $this->identifier();
        $log->order_data     = $this->order;
        $log->order_id       = $this->order->id;
        $log->message        = $response->getMessage();
        $log->code           = $response->getCode();
        $log->save();

        return $log;
    }

}