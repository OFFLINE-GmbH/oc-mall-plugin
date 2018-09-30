<?php

namespace OFFLINE\Mall\Classes\Payments;

use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\PaymentState\FailedState;
use OFFLINE\Mall\Classes\PaymentState\PaidState;
use OFFLINE\Mall\Models\PaymentGatewaySettings;
use Omnipay\Omnipay;
use Throwable;
use Validator;

class Stripe extends PaymentProvider
{
    public function name(): string
    {
        return 'Stripe';
    }

    public function identifier(): string
    {
        return 'stripe';
    }

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

    public function process(): PaymentResult
    {
        $gateway = Omnipay::create('Stripe');
        $gateway->setApiKey(decrypt(PaymentGatewaySettings::get('stripe_api_key')));

        $result = new PaymentResult();

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
            $result->successful    = false;
            $result->failedPayment = $this->logFailedPayment([], $e);

            return $result;
        }


        $data               = (array)$response->getData();
        $result->successful = $response->isSuccessful();

        if ($result->successful) {
            $payment                               = $this->logSuccessfulPayment($data, $response);
            $this->order->payment_id               = $payment->id;
            $this->order->payment_data             = $data;
            $this->order->card_type                = $data['source']['brand'];
            $this->order->card_holder_name         = $data['source']['name'];
            $this->order->credit_card_last4_digits = $data['source']['last4'];
            $this->order->payment_state            = PaidState::class;
            $this->order->save();
        } else {
            $result->failedPayment      = $this->logFailedPayment($data, $response);
            $this->order->payment_state = FailedState::class;
            $this->order->save();
        }

        return $result;
    }

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

    public function encryptedSettings(): array
    {
        return ['stripe_api_key'];
    }
}
