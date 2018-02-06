<?php

namespace OFFLINE\Mall\Classes\PaymentState;

use Event;
use OFFLINE\Mall\Models\Order;

abstract class PaymentState
{
    private $order;

    abstract public function getAvailableTransitions(): array;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function transitionTo(string $newState)
    {
        Event::fire('mall.order.payment.state_changed', ['old' => get_called_class(), 'new' => $newState]);
        $this->order->forceFill(['payment_state' => $newState]);
        $this->order->save();
    }

    public static function label(): string
    {
        $parts = explode('\\', get_called_class());
        $state = snake_case($parts[count($parts) - 1]);

        return trans('offline.mall::lang.order.payment_states.' . $state);
    }
}
