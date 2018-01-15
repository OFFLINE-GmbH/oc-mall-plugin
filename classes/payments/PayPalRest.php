<?php

namespace OFFLINE\Mall\Classes\Payments;

use Omnipay\Omnipay;
use Request;
use Session;
use Validator;

class PayPalRest extends PaymentProvider
{
    public function name(): string
    {
        return 'PayPal Rest API';
    }

    public function identifier(): string
    {
        return 'paypal-rest';
    }

    public function validate(): bool
    {
        return true;
    }

    public function process()
    {
        $gateway = $this->getGateway();

        $response = $gateway->purchase([
            'amount'    => round((int)$this->order->getOriginal('total_post_taxes') / 100, 2),
            'currency'  => $this->order->currency,
            'returnUrl' => $this->returnUrl(),
            'cancelUrl' => $this->cancelUrl(),
        ])->send();

        $result = new PaymentResult();

        // PayPal has to return a RedirectResponse if everything went well
        if ($response->isRedirect()) {
            Session::put('oc-mall.payment.callback', self::class);
            Session::put('oc-mall.paypal.transactionReference', $response->getTransactionReference());
            $result->redirect    = true;
            $result->redirectUrl = $response->getRedirectResponse()->getTargetUrl();

            return $result;
        }

        $data                  = (array)$response->getData();
        $result->failedPayment = $this->logFailedPayment($data, $response);

        return $result;
    }

    public function complete(): PaymentResult
    {
        $result  = new PaymentResult();
        $key     = Session::pull('oc-mall.paypal.transactionReference');
        $payerId = Request::input('PayerID');

        if ( ! $key || ! $payerId) {
            info('Missing payment data', ['key' => $key, 'payer' => $payerId]);
            $result->successful = false;

            return $result;
        }

        $this->setOrder($this->getOrderFromSession());

        $response = $this->getGateway()->completePurchase([
            'transactionReference' => $key,
            'payerId'              => $payerId,
        ])->send();

        $data = (array)$response->getData();

        $result->successful = $response->isSuccessful();

        if ($result->successful) {
            $payment                   = $this->logSuccessfulPayment($data, $response);
            $this->order->payment_id   = $payment->id;
            $this->order->payment_data = $data;
            $this->order->save();
        } else {
            $result->failedPayment = $this->logFailedPayment($data, $response);
        }

        return $result;
    }

    protected function getGateway()
    {
        $gateway = Omnipay::create('PayPal_Rest');
        $gateway->initialize([
            'clientId' => env('PAYPAL_CLIENT_ID'),
            'secret'   => env('PAYPAL_SECRET'),
            'testMode' => true,
        ]);

        return $gateway;
    }
}
