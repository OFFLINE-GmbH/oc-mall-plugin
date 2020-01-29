<?php

namespace OFFLINE\Mall\Classes\Payments;

use OFFLINE\Mall\Models\Order;

/**
 * The PaymentService orchestrates the payment process.
 */
class PaymentService
{
    /**
     * The used PaymentGateway for this payment.
     * @var PaymentGateway
     */
    public $gateway;
    /**
     * The order that is being paid.
     * @var Order
     */
    public $order;
    /**
     * Page filename of the checkout page.
     * @var string
     */
    public $pageFilename;
    /**
     * A PaymentRedirector instance.
     * @var PaymentRedirector
     */
    protected $redirector;

    /**
     * PaymentService constructor.
     *
     * @param PaymentGateway $gateway
     * @param Order          $order
     * @param string         $pageFilename
     *
     * @throws \Cms\Classes\CmsException
     */
    public function __construct(PaymentGateway $gateway, Order $order, string $pageFilename)
    {
        $this->gateway      = $gateway;
        $this->order        = $order;
        $this->pageFilename = $pageFilename;
        $this->redirector   = new PaymentRedirector($pageFilename);
    }

    /**
     * Processes the payment.
     *
     * @param string $flow
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function process($flow = 'checkout')
    {
        session()->put('mall.processing_order.id', $this->order->hashId);
        session()->put('mall.checkout.flow', $flow);

        try {
            $result = $this->gateway->process($this->order);
        } catch (\Throwable $e) {
            $result = new PaymentResult($this->gateway->getActiveProvider(), $this->order);
            $result->fail($this->order->toArray(), $e);
        }

        session()->forget('mall.payment_method.data');

        return $this->redirector->handlePaymentResult($result);
    }
}
