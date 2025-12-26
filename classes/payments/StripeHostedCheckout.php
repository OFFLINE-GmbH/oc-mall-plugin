<?php

namespace OFFLINE\Mall\Classes\Payments;

use OFFLINE\Mall\Models\PaymentGatewaySettings;
use Session;
use Throwable;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

/**
 * Process the payment via Stripe Hosted Checkout (redirect mode).
 * 
 * Uses dynamic price mapping (price_data) to avoid needing Stripe Product/Price IDs.
 */
class StripeHostedCheckout extends PaymentProvider
{
    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return 'Stripe (Hosted Checkout)';
    }

    /**
     * {@inheritdoc}
     */
    public function identifier(): string
    {
        return 'stripe-hosted-checkout';
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
        try {
            $stripe = $this->getStripeClient();
            $lineItems = $this->buildLineItems();
            $params = [
                'mode' => 'payment',
                'line_items' => $lineItems,
                'success_url' => $this->returnUrl(),
                'cancel_url' => $this->cancelUrl(),
                'client_reference_id' => (string) $this->order->id,
                'customer_email' => $this->getCustomerEmail(),
                'metadata' => [
                    'order_id' => $this->order->id,
                    'order_number' => $this->order->order_number,
                    'payment_hash' => $this->order->payment_hash,
                ],
            ];

            $session = $stripe->checkout->sessions->create($params);
            // Store session ID for later verification
            Session::put('mall.payment.callback', self::class);
            Session::put('mall.stripe_checkout.session_id', $session->id);

            return $result->redirect($session->url);
        } catch (ApiErrorException $e) {
            return $result->fail([
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ], $e);
        } catch (Throwable $e) {
            \Log::error($e->getMessage());
            return $result->fail([], $e);
        }
    }

    /**
     * Stripe Checkout has processed the payment and redirected the user back.
     *
     * @param PaymentResult $result
     *
     * @return PaymentResult
     */
    public function complete(PaymentResult $result): PaymentResult
    {
        $sessionId = Session::pull('mall.stripe_checkout.session_id');

        if (!$sessionId) {
            return $result->fail([
                'msg' => 'Missing Stripe session ID',
            ], null);
        }

        $this->setOrder($result->order);

        try {
            $stripe = $this->getStripeClient();

            // Retrieve the Checkout Session to verify payment status
            $session = $stripe->checkout->sessions->retrieve($sessionId, [
                'expand' => ['payment_intent'],
            ]);

            $data = [
                'session_id' => $session->id,
                'payment_status' => $session->payment_status,
                'payment_intent' => $session->payment_intent->id ?? null,
                'amount_total' => $session->amount_total,
                'currency' => $session->currency,
            ];

            // Check if payment was successful
            if ($session->payment_status === 'paid') {
                return $result->success($data, $session);
            }

            // Payment is not complete yet
            return $result->fail($data, $session);

        } catch (ApiErrorException $e) {
            return $result->fail([
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ], $e);
        } catch (Throwable $e) {
            return $result->fail([], $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function settings(): array
    {
        return [];
    }


    /**
     * Build the Stripe Client instance.
     *
     * @return StripeClient
     */
    protected function getStripeClient(): StripeClient
    {
        $secretKey = decrypt(PaymentGatewaySettings::get('stripe_api_key'));
        return new StripeClient($secretKey);
    }

    /**
     * Build line_items array with dynamic price_data from order products.
     *
     * @return array
     */
    protected function buildLineItems(): array
    {
        $currency = is_array($this->order->currency) ? $this->order->currency['code'] : 'gbp';

        return [
            [
                'price_data' => [
                    'currency' => strtolower($currency),
                    'unit_amount' => $this->convertToCents($this->order->total_post_taxes),
                    'product_data' => [
                        'name' => 'Order #' . $this->order->order_number,
                    ],
                ],
                'quantity' => 1,
            ]
        ];
    }
    /**
     * Convert price to cents (smallest currency unit).
     * 
     * Handles the conversion from database stored value to Stripe's expected format.
     *
     * @param float|int $amount Price in currency main unit
     * @return int Price in cents
     */
    protected function convertToCents($amount): int
    {
        // Mall stores prices as integers in cents, so we just need to ensure it's an int
        return (int) round($amount * 100);
    }


    /**
     * Get customer email from order.
     * 
     * Tries to get email from user relationship first, then falls back to billing address.
     *
     * @return string|null
     */
    protected function getCustomerEmail(): ?string
    {
        // Try to get email from customer's user account
        if (is_object($this->order->customer) && $this->order->customer->user) {
            return $this->order->customer->user->email;
        }

        // Fallback to billing address email
        if (is_array($this->order->billing_address) && isset($this->order->billing_address['email'])) {
            return $this->order->billing_address['email'];
        }

        return null;
    }
}
