<?php

namespace OFFLINE\Mall\Classes\Payments;

use OFFLINE\Mall\Classes\PaymentState\PaidState;
use OFFLINE\Mall\Models\CartProduct;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\OrderProduct;
use OFFLINE\Mall\Models\PaymentGatewaySettings;
use Omnipay\Omnipay;
use Request;
use Session;
use Throwable;

/**
 * Process the payment via Stripe Checkout.
 */
class StripeCheckout extends PaymentProvider
{

    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return 'Stripe Checkout';
    }

    /**
     * {@inheritdoc}
     */
    public function identifier(): string
    {
        return 'stripe-checkout';
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
        $stripe = $this->getStripeClient();

        $customerSearch = $stripe->customers->search([
            'query' => "email:'{$result->order->customer->user->email}'",
        ]);

        if (count($customerSearch->data) > 0) {
            $customer = $customerSearch->data[0];
        } else {
            $customer = $stripe->customers->create([
                'email' => $result->order->customer->user->email,
                'address' => $this->getAddressInformation($result->order->customer->billing_address)['address'],
                'shipping' => $this->getAddressInformation($result->order->customer->shipping_address),
            ]);
        }

        if (!$customer) {
            return $result->fail([
                'msg' => 'failed to create customer',
            ], ['customer' => $customer]);
        }

        $session = $stripe->checkout->sessions->create([
            'metadata' => [
                'order_id' => $result->order->id,
            ],
            'customer' => $customer->id,
            'line_items' => $result->order->products->map(function(OrderProduct $orderProduct) use ($result) {
                return [
                    'price_data' => [
                        'currency' => $result->order->currency['code'],
                        'product_data' => [
                            'name' => $orderProduct->name,
                        ],
                        'unit_amount' => $orderProduct->pricePostTaxes()->integer,
                    ],
                    'quantity' => $orderProduct->quantity,
                ];
            })->toArray(),
            'mode' => 'payment',
            'success_url' => $this->returnUrl(),
            'cancel_url' => $this->cancelUrl(),
        ]);

        Session::put('mall.payment.callback', self::class);
        Session::put('mall.stripe.checkout.transactionReference', $session->id);

        return $result->redirect($session->url);
    }


    protected function getAddressInformation($address): array
    {
        $name = $address->name;

        if ($address->company) {
            $name = sprintf(
                '%s (%s)',
                $address->company,
                $address->name
            );
        }

        return [
            'name'    => $name,
            'address' => [
                'line1'       => $address->lines_array[0] ?? '',
                'line2'       => $address->lines_array[1] ?? '',
                'city'        => $address->city,
                'country'     => $address->country->name,
                'postal_code' => $address->zip,
                'state'       => optional($address->state)->name,
            ]
        ];
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
        $stripe = $this->getStripeClient();

        $key = Session::pull('mall.stripe.checkout.transactionReference');

        $session = $stripe->checkout->sessions->retrieve($key);
        if (!$session || !$session->payment_intent) {
            return $result->fail([
                'msg' => 'failed to find session',
                'key' => $key,
            ], null);
        }

        $this->setOrder($result->order);

        $intent = $stripe->paymentIntents->retrieve($session->payment_intent);
        if ($intent->status === 'requires_capture') {
            $intent = $stripe->paymentIntents->capture($session->payment_intent, []);
        }

        if ($intent->status === 'processing') {
            return $result->pending();
        }

        if ($intent->status !== 'succeeded') {
            return $result->fail([
                'msg' => 'failed to capture payment intent',
                'status' => $intent->status,
            ], [
                'intent' => $intent,
            ]);
        }

        return $result->success(['intent' => $intent], null);
    }

    public static function completeWebhook($sessionId)
    {
        $provider = new self();

        $stripe = $provider->getStripeClient();

        $session = $stripe->checkout->sessions->retrieve($sessionId);
        if (!$session) {
            throw new \LogicException('Failed to find session');
        }

        $order = Order::where('id', $session->metadata->order_id)->firstOrFail();
        if ($order->payment_state === PaidState::class) {
            return $order;
        }

        if ($session->payment_status !== 'paid') {
            throw new \LogicException('Payment not paid');
        }

        $order->payment_state = PaidState::class;
        $order->save();

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function settings(): array
    {
        return [
            'stripe_api_key' => [
                'label' => 'offline.mall::lang.payment_gateway_settings.stripe.api_key',
                'comment' => 'offline.mall::lang.payment_gateway_settings.stripe.api_key_comment',
                'span' => 'left',
                'type' => 'text',
                'placeholder' => 'sk_test_xxxxxxxx',
            ],
            'stripe_webhook_secret' => [
                'label' => 'offline.mall::lang.payment_gateway_settings.stripe.webhook_secret',
                'comment' => 'offline.mall::lang.payment_gateway_settings.stripe.webhook_secret_comment',
                'span' => 'left',
                'type' => 'text',
                'placeholder' => 'whsec_xxxxxxxx',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function encryptedSettings(): array
    {
        return ['stripe_api_key', 'stripe_webhook_secret'];
    }

    /**
     * @return \Stripe\StripeClient
     */
    protected function getStripeClient(): \Stripe\StripeClient
    {
        return new \Stripe\StripeClient(decrypt(PaymentGatewaySettings::get('stripe_api_key')));
    }
}
