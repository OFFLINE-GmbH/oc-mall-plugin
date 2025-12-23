<?php

namespace OFFLINE\Mall\Classes\Payments\Webhooks;

use Illuminate\Http\Response;
use OFFLINE\Mall\Classes\Payments\PaymentResult;
use OFFLINE\Mall\Classes\Payments\StripeHostedCheckout;
use OFFLINE\Mall\Classes\PaymentState\PaidState;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentGatewaySettings;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Throwable;

/**
 * Handles Stripe Checkout webhook events.
 * 
 * This serves as a backup payment verification mechanism in case the user
 * doesn't complete the browser redirect after payment.
 */
class StripeHostedCheckoutWebhook
{
    /**
     * Handle incoming webhook from Stripe.
     *
     * @return Response
     */
    public function handle(): Response
    {
        try {
            $payload = @file_get_contents('php://input');
            $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
            $webhookSecret = $this->getWebhookSecret();
            
            if (!$webhookSecret) {
                logger()->error('OFFLINE.Mall: Stripe webhook secret not configured');
                return response('Webhook secret not configured', 500);
            }
            
            // Verify webhook signature
            try {
                $event = Webhook::constructEvent(
                    $payload,
                    $sigHeader,
                    $webhookSecret
                );
            } catch (SignatureVerificationException $e) {
                logger()->error('OFFLINE.Mall: Stripe webhook signature verification failed', [
                    'error' => $e->getMessage()
                ]);
                return response('Invalid signature', 400);
            }
            
            // Handle the event
            if ($event->type === 'checkout.session.completed') {
                $this->handleCheckoutSessionCompleted($event->data->object);
            }
            
            return response('Webhook handled', 200);
            
        } catch (Throwable $e) {
            logger()->error('OFFLINE.Mall: Stripe webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response('Webhook error', 500);
        }
    }

    /**
     * Handle checkout.session.completed event.
     *
     * @param object $session Stripe Session object
     * @return void
     */
    protected function handleCheckoutSessionCompleted($session): void
    {
        // Only process paid sessions
        if ($session->payment_status !== 'paid') {
            logger()->info('OFFLINE.Mall: Stripe session not paid yet', [
                'session_id' => $session->id,
                'payment_status' => $session->payment_status,
            ]);
            return;
        }
        
        // Get order ID from metadata or client_reference_id
        $orderId = $session->metadata->order_id ?? $session->client_reference_id;
        
        if (!$orderId) {
            logger()->error('OFFLINE.Mall: No order ID in Stripe webhook', [
                'session_id' => $session->id,
            ]);
            return;
        }
        
        // Find the order
        $order = Order::find($orderId);
        
        if (!$order) {
            logger()->error('OFFLINE.Mall: Order not found in Stripe webhook', [
                'order_id' => $orderId,
                'session_id' => $session->id,
            ]);
            return;
        }
        
        // Check if order is already paid to avoid duplicate processing
        if ($order->payment_state === PaidState::class) {
            logger()->info('OFFLINE.Mall: Order already marked as paid', [
                'order_id' => $orderId,
                'session_id' => $session->id,
            ]);
            return;
        }
        
        try {
            // Use payment provider method to mark order as paid
            $paymentProvider = new StripeHostedCheckout($order);
            $paymentResult = new PaymentResult($paymentProvider, $order);
            
            $paymentData = [
                'session_id' => $session->id,
                'payment_intent' => $session->payment_intent,
                'amount_total' => $session->amount_total,
                'currency' => $session->currency,
                'payment_status' => $session->payment_status,
                'customer_email' => $session->customer_email,
            ];
            
            // Mark order as paid using PaymentResult success method
            $paymentResult->success($paymentData, $session);
            
            logger()->info('OFFLINE.Mall: Order marked as paid via webhook', [
                'order_id' => $orderId,
                'session_id' => $session->id,
            ]);
            
        } catch (Throwable $e) {
            logger()->critical('OFFLINE.Mall: Failed to mark order as paid via webhook', [
                'order_id' => $orderId,
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the webhook secret from settings.
     *
     * @return string|null
     */
    protected function getWebhookSecret(): ?string
    {
        $secret = PaymentGatewaySettings::get('stripe_webhook_secret');
        
        if (!$secret) {
            return null;
        }
        
        return decrypt($secret);
    }
}
