<?php

namespace OFFLINE\Mall\Classes\Payments;

use OFFLINE\Mall\Models\PaymentGatewaySettings;
use Omnipay\Omnipay;
use Request;
use Session;
use Throwable;
use Validator;

/**
 * Process the payment via Datatrans.
 */
class Datatrans extends PaymentProvider
{
    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return 'Datatrans';
    }

    /**
     * {@inheritdoc}
     */
    public function identifier(): string
    {
        return 'datatrans';
    }

    /**
     * {@inheritDoc}
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
                'amount'         => $this->order->total_in_currency,
                'currency'       => $this->order->currency['code'],
                'transactionId'  => $this->order->id,
                'returnUrl'      => $this->returnUrl(),
                'cancelUrl'      => $this->cancelUrl(),
                'redirectMethod' => 'GET',
            ])->send();
        } catch (Throwable $e) {
            return $result->fail([], $e);
        }


        // Datatrans has to return a RedirectResponse if everything went well
        if ( ! $response->isRedirect()) {
            return $result->fail((array)$response->getData(), $response);
        }

        Session::put('mall.payment.callback', self::class);

        return $result->redirect($response->getRedirectUrl());
    }

    /**
     * Datatrans has processed the payment and redirected the user back.
     *
     * @param PaymentResult $result
     *
     * @return PaymentResult
     */
    public function complete(PaymentResult $result): PaymentResult
    {
        $this->setOrder($result->order);

        try {
            $response = $this->getGateway()->completeAuthorize()->send();
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
     * Build the Omnipay Gateway for Datatrans.
     *
     * @return \Omnipay\Common\GatewayInterface
     */
    protected function getGateway()
    {
        $gateway = Omnipay::create('Datatrans');

        $gateway->initialize([
            'merchantId' => decrypt(PaymentGatewaySettings::get('datatrans_merchant_id')),
            'sign'       => decrypt(PaymentGatewaySettings::get('datatrans_sign')),
        ]);

        return $gateway;
    }

    /**
     * {@inheritdoc}
     */
    public function settings(): array
    {
        return [
            'datatrans_merchant_id' => [
                'label' => 'offline.mall::lang.payment_gateway_settings.datatrans.merchant_id',
                'span'  => 'left',
                'type'  => 'text',
            ],
            'datatrans_sign'        => [
                'label' => 'offline.mall::lang.payment_gateway_settings.datatrans.sign',
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
        return ['datatrans_merchant_id', 'datatrans_sign'];
    }
}
