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
                'amount'        => $this->order->total_in_currency,
                'currency'      => $this->order->currency['code'],
                'transactionId' => $this->order->id,
                'returnUrl'     => $this->returnUrl(),
                'cancelUrl'     => $this->cancelUrl(),
            ])->send();
        } catch (Throwable $e) {
            return $result->fail([], $e);
        }


        // Datatrans has to return a RedirectResponse if everything went well
        if ( ! $response->isRedirect()) {
            return $result->fail((array)$response->getData(), $response);
        }

        Session::put('mall.payment.callback', self::class);
        Session::put('mall.datatrans.transactionReference', $response->getTransactionReference());

        return dd($response->getRedirectResponse());

        return $result->redirect($response->getRedirectResponse()->getTargetUrl());
    }

    /**
     * Build the Omnipay Gateway for PayPal.
     *
     * @return \Omnipay\Common\GatewayInterface
     */
    protected function getGateway()
    {
        $gateway = Omnipay::create('Datatrans');
//        $gateway->setMerchantId(decrypt(PaymentGatewaySettings::get('datatrans_merchant_id')));
//        $gateway->setSign(decrypt(PaymentGatewaySettings::get('datatrans_sign')));

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
