<?php

namespace OFFLINE\Mall\Classes\Payments;

use OFFLINE\Mall\Models\Order;
use Throwable;

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
     * @param Order $order
     * @param string $pageFilename
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

        $provider = $this->gateway->getActiveProvider();

        if ($this->order->total_in_currency > 0) {
            try {
                $result = $this->gateway->process($this->order);
            } catch (Throwable $e) {
                $result = new PaymentResult($provider, $this->order);
                $result->fail($this->order->toArray(), $e);
            }
        } else {
            // Free orders do not need to be processed by a payment provider.
            $result = new PaymentResult($provider, $this->order);
            $result->success($this->order->toArray(), (object)['message' => 'Free order, no payment required.']);
        }

        session()->forget('mall.payment_method.data');

        return $this->redirector->handlePaymentResult($result);
    }
}
