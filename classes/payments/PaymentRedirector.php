<?php

namespace OFFLINE\Mall\Classes\Payments;

use Cms\Classes\Controller;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * The PaymentRedirector handles all external and
 * internal redirects from or to different payment
 * services.
 */
class PaymentRedirector
{
    /**
     * @var Controller
     */
    protected $controller;
    /**
     * @var string
     */
    protected $page;

    /**
     * PaymentRedirector constructor.
     *
     * @param string $page
     *
     * @throws \Cms\Classes\CmsException
     */
    public function __construct(string $page)
    {
        $this->controller = new Controller();
        $this->page       = $page;
    }

    /**
     * Handle the final redirect after all payment processing is done.
     *
     * @param $state
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function finalRedirect($state)
    {
        $states = [
            'failed'     => $this->getFailedUrl(),
            'cancelled'  => $this->getCancelledUrl(),
            'successful' => $this->getSuccessfulUrl(),
        ];

        $orderId = session()->pull('mall.processing_order.id');
        $flow    = session()->get('mall.checkout.flow');

        $url = $states[$state];
        if ($orderId) {
            $url .= '?' . http_build_query(['order' => $orderId, 'flow' => $flow]);
        }

        return redirect()->to($url);
    }

    /**
     * Handles a PaymentResult.
     *
     * @param PaymentResult $result
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handlePaymentResult(PaymentResult $result)
    {
        if ($result->redirect) {
            if ($result->redirectUrl) {
                return redirect()->to($result->redirectUrl);
            }

            if ( ! $result->redirectResponse) {
                throw new \LogicException('redirectUrl or redirectResponse on PaymentResult is required.');
            }

            // If the PaymentProvider returned a RedirectResponse we can re-use it
            // as is. This will redirect the user to the payment provider's external page.
            if ($result->redirectResponse instanceof RedirectResponse) {
                return $result->redirectResponse;
            }

            // If the returned response is not a RedirectResponse, we have to render the content
            // of the response in the browser. To do this, the special route "/mall/checkout/response"
            // is registered, that simply renders the content of the "mall.checkout.response" session
            // key in the browser. This is usually used by PaymentProviders to render a custom form
            // that is submitted immediately after it was loaded. This in turn sends a POST
            // request to the payment provider with all the required payment information.

            // Let's put the response's content into the session store and redirect
            // the user to the route that will display it.
            session()->put('mall.checkout.response', $result->redirectResponse->getContent());

            return redirect()->to('/mall/checkout/response');
        }

        if ($result->successful) {
            // Only trigger the checkout succeeded event for the checkout flow (not for later payments)
            $flow = session()->get('mall.checkout.flow');
            if ($flow === 'checkout') {
                Event::fire('mall.checkout.succeeded', [$result]);
            }

            return $this->finalRedirect('successful');
        }


        if (optional($result->order)->wasRecentlyCreated) {
            Event::fire('mall.checkout.failed', [$result]);
        }

        return $this->finalRedirect('failed');
    }

    /**
     * Handles any off-site returns (PayPal, etc).
     *
     * @param $type
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
            /** @var PaymentProvider $paymentProvider */
            $paymentProvider = new $callback;
            if ( ! method_exists($paymentProvider, 'complete')) {
                throw new \LogicException('Payment providers that redirect off-site need to have a "complete" method!');
            }

            $result = new PaymentResult($paymentProvider, $paymentProvider->getOrderFromSession());

            return $this->handlePaymentResult($paymentProvider->complete($result));
        }

        return $this->finalRedirect('successful');
    }

    /**
     * Returns the URL to a substep of the payment process.
     *
     * @param       $step
     * @param array $params
     *
     * @return string
     */
    public function stepUrl($step, $params = []): string
    {
        return $this->controller->pageUrl(
            $this->page,
            array_merge($params, ['step' => $step])
        );
    }


    /**
     * The user is redirected to this URL if a payment failed.
     *
     * @return string
     */
    protected function getFailedUrl()
    {
        return $this->stepUrl('failed');
    }

    /**
     * The user is redirected to this URL if a payment was cancelled.
     *
     * @return string
     */
    protected function getCancelledUrl()
    {
        return $this->stepUrl('cancelled');
    }

    /**
     * The user is redirected to this URL if a payment was successful.
     *
     * @return string
     */
    protected function getSuccessfulUrl()
    {
        return $this->stepUrl('done');
    }
}
