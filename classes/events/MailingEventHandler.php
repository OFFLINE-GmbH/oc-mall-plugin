<?php

namespace OFFLINE\Mall\Classes\Events;


use Illuminate\Support\Facades\Mail;
use OFFLINE\Mall\Classes\PaymentState\FailedState;
use OFFLINE\Mall\Classes\PaymentState\PaidState;
use OFFLINE\Mall\Classes\PaymentState\RefundedState;
use OFFLINE\Mall\Models\Notification;

class MailingEventHandler
{
    public $enabledNotifications = [];

    public function __construct()
    {
        $this->enabledNotifications = Notification::getEnabled();
    }

    /**
     * Subscribe conditionally to all relevant mall events.
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $eventMap = [
            'offline.mall::customer.created'    => [
                'event'   => 'mall.customer.afterSignup',
                'handler' => 'MailingEventHandler@customerCreated',
            ],
            'offline.mall::checkout.succeeded'  => [
                'event'   => 'mall.checkout.succeeded',
                'handler' => 'MailingEventHandler@checkoutSucceeded',
            ],
            'offline.mall::checkout.failed'     => [
                'event'   => 'mall.checkout.failed',
                'handler' => 'MailingEventHandler@checkoutFailed',
            ],
            'offline.mall::order.state.changed' => [
                'event'   => 'mall.order.state.changed',
                'handler' => 'MailingEventHandler@orderStateChanged',
            ],
            'offline.mall::order.shipped'       => [
                'event'   => 'mall.order.shipped',
                'handler' => 'MailingEventHandler@orderShipped',
            ],
        ];

        foreach ($eventMap as $notification => $data) {
            if ($this->enabledNotifications->has($notification)) {
                $events->listen($data['event'], $data['handler']);
            }
        }

        $events->listen('mall.order.payment_state.changed', 'MailingEventHandler@orderPaymentStateChanged');
    }

    /**
     * A customer has signed up.
     *
     * @param $user
     */
    public function customerCreated($user)
    {
        // Don't mail guest accounts.
        if ($user->customer->is_guest) {
            return;
        }

        $data = [
            'user' => $user,
        ];

        Mail::queue($this->template('offline.mall::customer.created'), $data, function ($message) use ($user) {
            $message->to($user->email, $user->customer->name);
        });
    }

    /**
     * A checkout was successful.
     *
     * @param $result
     */
    public function checkoutSucceeded($result)
    {
        $data = [
            'order' => $result->order->fresh(['products', 'customer']),
        ];

        Mail::queue($this->template('offline.mall::checkout.succeeded'), $data, function ($message) use ($result) {
            $message->to($result->order->customer->user->email, $result->order->customer->name);
        });
    }

    /**
     * A checkout has failed.
     *
     * @param $result
     */
    public function checkoutFailed($result)
    {
        $data = [
            'order' => $result->order->fresh(['products', 'customer']),
        ];

        Mail::queue($this->template('offline.mall::checkout.failed'), $data, function ($message) use ($result) {
            $message->to($result->order->customer->user->email, $result->order->customer->name);
        });
    }

    /**
     * The state of an order has changed.
     *
     * @param $order
     */
    public function orderStateChanged($order)
    {
        $data = [
            'order' => $order->load(['order_state']),
        ];

        Mail::queue($this->template('offline.mall::order.changed'), $data, function ($message) use ($order) {
            $message->to($order->customer->user->email, $order->customer->name);
        });
    }

    /**
     * The order has been shipped.
     *
     * @param $order
     */
    public function orderShipped($order)
    {
        if ( ! $order->shippingNotification) {
            return;
        }

        $data = [
            'order' => $order->load(['order_state']),
        ];

        Mail::queue($this->template('offline.mall::order.shipped'), $data, function ($message) use ($order) {
            $message->to($order->customer->user->email, $order->customer->name);
        });
    }

    /**
     * The payment state of an order has changed.
     * Depending on the new order state a different mail template will be used.
     *
     * @param $order
     */
    public function orderPaymentStateChanged($order)
    {
        $attr = 'payment_state';
        if ( ! $order->isDirty($attr) || $order->getOriginal($attr) === $order->getAttribute($attr)) {
            return;
        }

        switch ($order->getAttribute($attr)) {
            case FailedState::class:
                $view = 'failed';
                break;
            case PaidState::class:
                $view = 'paid';
                break;
            case RefundedState::class:
                $view = 'refunded';
                break;
            default:
                // The customer is not informed about any other state.
                return;
        }

        // This notification is disabled.
        if ( ! $this->enabledNotifications->has('offline.mall::payment.' . $view)) {
            return;
        }

        $data = [
            'order' => $order->load(['order_state', 'payment_logs']),
        ];

        Mail::queue(
            $this->template('offline.mall::payment.' . $view),
            $data,
            function ($message) use ($order) {
                $message->to($order->customer->user->email, $order->customer->name);
            }
        );
    }

    /**
     * Return the user defined mail template for a given event code.
     *
     * @return string
     */
    protected function template($code)
    {
        return $this->enabledNotifications->get($code);
    }
}