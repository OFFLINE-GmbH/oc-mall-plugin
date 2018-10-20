<?php

namespace OFFLINE\Mall\Classes\Payments;

use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Models\PaymentGatewaySettings;
use Omnipay\Omnipay;
use Throwable;
use Validator;

/**
 * Process the payment via Stripe.
 */
class Stripe extends PaymentProvider
{
    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return 'Stripe';
    }

    /**
     * {@inheritdoc}
     */
    public function identifier(): string
    {
        return 'stripe';
    }

    /**
     * {@inheritdoc}
     */
    public function validate(): bool
    {
        $rules = [
            'token' => 'required|size:28|regex:/tok_[0-9a-zA-z]{24}/',
        ];

        $validation = Validator::make($this->data, $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function process(PaymentResult $result): PaymentResult
    {
        $gateway = Omnipay::create('Stripe');
        $gateway->setApiKey(decrypt(PaymentGatewaySettings::get('stripe_api_key')));

        $response = null;
        try {
            $response = $gateway->purchase([
                'amount'    => $this->order->total_in_currency,
                'currency'  => $this->order->currency['code'],
                'token'     => $this->data['token'] ?? false,
                'returnUrl' => $this->returnUrl(),
                'cancelUrl' => $this->cancelUrl(),
            ])->send();
        } catch (Throwable $e) {
            return $result->fail([], $e);
        }

        $data = (array)$response->getData();

        if ( ! $response->isSuccessful()) {
            return $result->fail($data, $response);
        }

        $this->order->card_type                = $data['source']['brand'];
        $this->order->card_holder_name         = $data['source']['name'];
        $this->order->credit_card_last4_digits = $data['source']['last4'];

        return $result->success($data, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function settings(): array
    {
        return [
            'stripe_api_key'         => [
                'label'   => 'offline.mall::lang.payment_gateway_settings.stripe.api_key',
                'comment' => 'offline.mall::lang.payment_gateway_settings.stripe.api_key_comment',
                'span'    => 'left',
                'type'    => 'text',
            ],
            'stripe_publishable_key' => [
                'label'   => 'offline.mall::lang.payment_gateway_settings.stripe.publishable_key',
                'comment' => 'offline.mall::lang.payment_gateway_settings.stripe.publishable_key_comment',
                'span'    => 'left',
                'type'    => 'text',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function encryptedSettings(): array
    {
        return ['stripe_api_key'];
    }
}
