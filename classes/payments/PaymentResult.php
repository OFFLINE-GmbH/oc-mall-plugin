<?php

namespace OFFLINE\Mall\Classes\Payments;

use OFFLINE\Mall\Classes\PaymentState\FailedState;
use OFFLINE\Mall\Classes\PaymentState\PaidState;
use OFFLINE\Mall\Classes\PaymentState\PendingState;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentLog;
use Symfony\Component\HttpFoundation\Response;

/**
 * The PaymentResult contains the result of a payment attempt.
 */
class PaymentResult
{
    /**
     * If the payment was successful.
     * @var bool
     */
    public $successful = false;
    /**
     * If this payment needs a redirect.
     * @var bool
     */
    public $redirect = false;
    /**
     * Use this response as redirect.
     * @var \Illuminate\Http\Response
     */
    public $redirectResponse;
    /**
     * Redirect the user to this URL.
     * @var string
     */
    public $redirectUrl = '';
    /**
     * The failed payment log.
     * @var PaymentLog
     */
    public $failedPayment;
    /**
     * The order that is being processed.
     * @var Order
     */
    public $order;
    /**
     * Error message in case of a failure.
     * @var string
     */
    public $message;
    /**
     * The used PaymentProvider for this payment.
     * @var PaymentProvider
     */
    public $provider;

    /**
     * PaymentResult constructor.
     *
     * @param PaymentProvider $provider
     * @param Order           $order
     */
    public function __construct(PaymentProvider $provider, Order $order)
    {
        $this->provider   = $provider;
        $this->order      = $order;
        $this->successful = false;
    }

    /**
     * The payment was successful.
     *
     * The payment is logged, associated with the order
     * and the order is marked as paid.
     *
     * @param array $data
     * @param       $response
     *
     * @return PaymentResult
     */
    public function success(array $data, $response): self
    {
        $this->successful = true;

        try {
            $payment = $this->logSuccessfulPayment($data, $response);
        } catch (\Throwable $e) {
            // Even if the log failed we *have* to mark this order as paid since the payment went already through.
            logger()->error(
                'OFFLINE.Mall: Could not log successful payment.',
                ['data' => $data, 'response' => $response, 'order' => $this->order, 'exception' => $e]
            );
        }

        try {
            $this->order->payment_id    = $payment->id;
            $this->order->payment_state = PaidState::class;
            $this->order->save();
        } catch (\Throwable $e) {
            // If the order could not be marked as paid the shop admin will have to do this manually.
            logger()->critical(
                'OFFLINE.Mall: Could not mark paid order as paid.',
                ['data' => $data, 'response' => $response, 'order' => $this->order, 'exception' => $e]
            );
        }

        return $this;
    }

    /**
     * The payment is pending.
     *
     * No payment is logged. The order's payment state
     * is marked as pending.
     *
     * @return PaymentResult
     */
    public function pending(): self
    {
        $this->successful = true;

        try {
            $this->order->payment_state = PendingState::class;
            $this->order->save();
        } catch (\Throwable $e) {
            // If the order could not be marked as pending the shop admin will have to do this manually.
            logger()->critical(
                'OFFLINE.Mall: Could not mark pending order as pending.',
                ['order' => $this->order, 'exception' => $e]
            );
        }

        return $this;
    }

    /**
     * The payment has failed.
     *
     * The failed payment is logged and the order's
     * payment state is marked as failed.
     *
     * @param array $data
     * @param       $response
     *
     * @return PaymentResult
     */
    public function fail(array $data, $response): self
    {
        $this->successful = false;

        logger()->error(
            'OFFLINE.Mall: A payment failed.',
            ['data' => $data, 'response' => $response, 'order' => $this->order]
        );

        try {
            $this->failedPayment = $this->logFailedPayment($data, $response);
        } catch (\Throwable $e) {
            logger()->error(
                'OFFLINE.Mall: Could not log failed payment.',
                ['data' => $data, 'response' => $response, 'order' => $this->order, 'exception' => $e]
            );
        }

        try {
            $this->order->payment_state = FailedState::class;
            $this->order->save();
        } catch (\Throwable $e) {
            // If the order could not be marked as failed the shop admin will have to do this manually.
            logger()->critical(
                'OFFLINE.Mall: Could not mark failed order as failed.',
                ['data' => $data, 'response' => $response, 'order' => $this->order, 'exception' => $e]
            );
        }

        return $this;
    }

    /**
     * The payment requires a redirect to an external URL.
     *
     * @param $url
     *
     * @return PaymentResult
     */
    public function redirect($url): self
    {
        $this->redirect    = true;
        $this->redirectUrl = $url;

        return $this;
    }

    /**
     * The payment gateway returned a re-usable Symfony response.
     *
     * @param Response $response
     *
     * @return PaymentResult
     */
    public function redirectResponse(Response $response): self
    {
        $this->redirect         = true;
        $this->redirectResponse = $response;

        return $this;
    }

    /**
     * Create a PaymentLog entry for a failed payment.
     *
     * @param array $data
     * @param       $response
     *
     * @return PaymentLog
     */
    protected function logFailedPayment(array $data, $response): PaymentLog
    {
        return $this->logPayment(true, $data, $response);
    }

    /**
     * Create a PaymentLog entry for a successful payment.
     *
     * @param array $data
     * @param       $response
     *
     * @return PaymentLog
     */
    protected function logSuccessfulPayment(array $data, $response): PaymentLog
    {
        return $this->logPayment(false, $data, $response);
    }

    /**
     * Create a PaymentLog entry.
     *
     * @param bool  $failed
     * @param array $data
     * @param       $response
     *
     * @return PaymentLog
     */
    protected function logPayment(bool $failed, array $data, $response): PaymentLog
    {
        $log                   = new PaymentLog();
        $log->failed           = $failed;
        $log->data             = $data;
        $log->ip               = request()->ip();
        $log->session_id       = session()->get('cart_session_id');
        $log->payment_provider = $this->provider->identifier();
        $log->payment_method   = $this->order->payment_method;
        $log->order_data       = $this->order;
        $log->order_id         = $this->order->id;

        if ($response) {
            $log->message = method_exists($response, 'getMessage')
                ? $response->getMessage()
                : json_encode($response);

            $log->code = method_exists($response, 'getCode')
                ? $response->getCode()
                : null;
        }

        return tap($log)->save();
    }
}
