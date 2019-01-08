<?php

namespace OFFLINE\Mall\Classes\PaymentState;

abstract class PaymentState
{
    abstract public static function getAvailableTransitions(): array;

    public static function label(): string
    {
        $parts = explode('\\', get_called_class());
        $state = snake_case($parts[count($parts) - 1]);

        return trans('offline.mall::lang.order.payment_states.' . $state);
    }

    public static function color(): string
    {
        return '#333';
    }
}
