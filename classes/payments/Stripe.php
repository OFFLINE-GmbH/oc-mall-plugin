<?php

namespace OFFLINE\Mall\Classes\Payments;

use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\PaymentState\FailedState;
use OFFLINE\Mall\Classes\PaymentState\PaidState;
use OFFLINE\Mall\Models\PaymentGatewaySettings;
use Omnipay\Omnipay;
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
            'number'      => 'required|digits:16',
            'expiryMonth' => 'required|integer|min:1|max:12',
            'expiryYear'  => 'required|integer|min:' . date('Y'),
            'cvv'         => 'required|digits:3',
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

        $response = $gateway->purchase([
            'amount'    => round((int)$this->order->getOriginal('total_post_taxes') / 100, 2),
            'currency'  => $this->order->currency,
            'card'      => $this->data,
            'returnUrl' => $this->returnUrl(),
            'cancelUrl' => $this->cancelUrl(),
        ])->send();

        $data = (array)$response->getData();

        $result             = new PaymentResult();
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
}
