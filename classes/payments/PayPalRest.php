<?php

namespace OFFLINE\Mall\Classes\Payments;

use OFFLINE\Mall\Classes\PaymentState\FailedState;
use OFFLINE\Mall\Classes\PaymentState\PaidState;
use OFFLINE\Mall\Models\PaymentGatewaySettings;
use Omnipay\Omnipay;
use Request;
use Session;
use Throwable;
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

        $result   = new PaymentResult();
        $result->order = $this->order;

        $response = null;
        try {
            $response = $gateway->purchase([
                'amount'    => $this->order->total_in_currency,
                'currency'  => $this->order->currency['code'],
                'returnUrl' => $this->returnUrl(),
                'cancelUrl' => $this->cancelUrl(),
            ])->send();
        } catch (Throwable $e) {
            $result->successful    = false;
            $result->failedPayment = $this->logFailedPayment([], $e);

            return $result;
        }

        // PayPal has to return a RedirectResponse if everything went well
        if ($response->isRedirect()) {
            Session::put('mall.payment.callback', self::class);
            Session::put('mall.paypal.transactionReference', $response->getTransactionReference());
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
        $key     = Session::pull('mall.paypal.transactionReference');
        $payerId = Request::input('PayerID');

        if ( ! $key || ! $payerId) {
            info('Missing payment data', ['key' => $key, 'payer' => $payerId]);
            $result->successful = false;

            return $result;
        }

        $this->setOrder($this->getOrderFromSession());

        try {
            $response = $this->getGateway()->completePurchase([
                'transactionReference' => $key,
                'payerId'              => $payerId,
            ])->send();
        } catch (Throwable $e) {
            $result->successful    = false;
            $result->failedPayment = $this->logFailedPayment([], $e);

            return $result;
        }

        $data = (array)$response->getData();

        $result->successful = $response->isSuccessful();

        if ($result->successful) {
            $payment                    = $this->logSuccessfulPayment($data, $response);
            $this->order->payment_id    = $payment->id;
            $this->order->payment_data  = $data;
            $this->order->payment_state = PaidState::class;
            $this->order->save();
        } else {
            $result->failedPayment      = $this->logFailedPayment($data, $response);
            $this->order->payment_state = FailedState::class;
            $this->order->save();
        }

        return $result;
    }

    protected function getGateway()
    {
        $gateway = Omnipay::create('PayPal_Rest');
        $gateway->initialize([
            'clientId' => decrypt(PaymentGatewaySettings::get('paypal_client_id')),
            'secret'   => decrypt(PaymentGatewaySettings::get('paypal_secret')),
            'testMode' => (bool)PaymentGatewaySettings::get('paypal_test_mode'),
        ]);

        return $gateway;
    }

    public function settings(): array
    {
        return [
            'paypal_test_mode' => [
                'label'   => 'offline.mall::lang.payment_gateway_settings.paypal.test_mode',
                'comment' => 'offline.mall::lang.payment_gateway_settings.paypal.test_mode_comment',
                'span'    => 'left',
                'type'    => 'switch',
            ],
            'paypal_client_id' => [
                'label' => 'offline.mall::lang.payment_gateway_settings.paypal.client_id',
                'span'  => 'left',
                'type'  => 'text',
            ],
            'paypal_secret'    => [
                'label' => 'offline.mall::lang.payment_gateway_settings.paypal.secret',
                'span'  => 'left',
                'type'  => 'text',
            ],
        ];
    }

    public function encryptedSettings(): array
    {
        return ['paypal_client_id', 'paypal_secret'];
    }
}
