<?php

namespace OFFLINE\Mall\Classes\Payments;

use OFFLINE\Mall\Models\PaymentGatewaySettings;
use Omnipay\Omnipay;
use Request;
use Session;
use Throwable;
use Validator;

/**
 * Process the payment via PayPal's REST API.
 */
class PayPalRest extends PaymentProvider
{
    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return 'PayPal Rest API';
    }

    /**
     * {@inheritdoc}
     */
    public function identifier(): string
    {
        return 'paypal-rest';
    }

    /**
     * {@inheritdoc}
     */
    public function validate(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function process(PaymentResult $result): PaymentResult
    {
        $gateway = $this->getGateway();

        $response = null;
        try {
            $response = $gateway->purchase([
                'amount'    => $this->order->total_in_currency,
                'currency'  => $this->order->currency['code'],
                'returnUrl' => $this->returnUrl(),
                'cancelUrl' => $this->cancelUrl(),
            ])->send();
        } catch (Throwable $e) {
            return $result->fail([], $e);
        }

        // PayPal has to return a RedirectResponse if everything went well
        if ( ! $response->isRedirect()) {
            return $result->fail((array)$response->getData(), $response);
        }

        Session::put('mall.payment.callback', self::class);
        Session::put('mall.paypal.transactionReference', $response->getTransactionReference());

        return $result->redirect($response->getRedirectResponse()->getTargetUrl());
    }

    /**
     * PayPal has processed the payment and redirected the user back.
     *
     * @param PaymentResult $result
     *
     * @return PaymentResult
     */
    public function complete(PaymentResult $result): PaymentResult
    {
        $key     = Session::pull('mall.paypal.transactionReference');
        $payerId = Request::input('PayerID');

        if ( ! $key || ! $payerId) {
            return $result->fail([
                'msg'   => 'Missing payment data',
                'key'   => $key,
                'payer' => $payerId,
            ], null);
        }

        $this->setOrder($result->order);

        try {
            $response = $this->getGateway()->completePurchase([
                'transactionReference' => $key,
                'payerId'              => $payerId,
            ])->send();
        } catch (Throwable $e) {
            return $result->fail([], $e);
        }

        $data = (array)$response->getData();

        if ( ! $response->isSuccessful()) {
            return $result->fail($data, $response);
        }

        return $result->success($data, $response);
    }

    /**
     * Build the Omnipay Gateway for PayPal.
     *
     * @return \Omnipay\Common\GatewayInterface
     */
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function encryptedSettings(): array
    {
        return ['paypal_client_id', 'paypal_secret'];
    }
}
