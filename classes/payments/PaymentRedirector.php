<?php

namespace OFFLINE\Mall\Classes\Payments;

use Cms\Classes\Controller;
use Illuminate\Support\Facades\Event;

class PaymentRedirector
{
    public $controller;
    protected $page;

    public function __construct(string $page)
    {
        $this->controller = new Controller();
        $this->page       = $page;
    }

    public function finalRedirect($state)
    {
        $states = [
            'failed'     => $this->getFailedUrl(),
            'cancelled'  => $this->getCancelledUrl(),
            'successful' => $this->getSuccessfulUrl(),
        ];

        $orderId = session()->pull('mall.processing_order.id');

        $url = $states[$state];
        if ($orderId) {
            $url .= '?order=' . $orderId;
        }

        return redirect()->to($url);
    }

    public function stepUrl($step, $params = [])
    {
        return $this->controller->pageUrl(
            $this->page,
            array_merge($params, ['step' => $step])
        );
    }

    public function handlePaymentResult(PaymentResult $result)
    {
        if ($result->redirect) {
            return $result->redirectUrl ? redirect()->to($result->redirectUrl) : $result->redirectResponse;
        }

        if ($result->successful) {
            if (optional($result->order)->wasRecentlyCreated) {
                Event::fire('mall.checkout.succeeded', [$result]);
            }

            return $this->finalRedirect('successful');
        }


        if (optional($result->order)->wasRecentlyCreated) {
            Event::fire('mall.checkout.failed', [$result]);
        }

        return $this->finalRedirect('failed');
    }

    public function handleOffSiteReturn($type)
    {
        // Someone tampered with the url or the session has expired.
        $paymentId = session()->pull('mall.payment.id');
        if ($paymentId !== request()->input('oc-mall_payment_id')) {
            session()->forget('mall.payment.callback');

            return $this->finalRedirect('failed');
        }

        // The user has cancelled the payment
        if ($type === 'cancel') {
            session()->forget('mall.payment.callback');

            return $this->finalRedirect('cancelled');
        }

        // If a callback is set we need to do an additional step to
        // complete this payment.
        $callback = session()->pull('mall.payment.callback');
        if ($callback) {
            $paymentMethod = new $callback;

            if ( ! method_exists($paymentMethod, 'complete')) {
                throw new \LogicException('Payment gateways that redirect off-site need to have a "complete" method!');
            }

            return $this->handlePaymentResult($paymentMethod->complete());
        }

        // The payment was successful
        return $this->finalRedirect('successful');
    }

    protected function getFailedUrl()
    {
        return $this->stepUrl('failed');
    }

    protected function getCancelledUrl()
    {
        return $this->stepUrl('cancelled');
    }

    protected function getSuccessfulUrl()
    {
        return $this->stepUrl('done');
    }
}
