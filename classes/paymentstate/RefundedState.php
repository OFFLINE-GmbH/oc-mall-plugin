<?php

namespace OFFLINE\Mall\Classes\PaymentState;

class RefundedState extends PaymentState
{
    public static function getAvailableTransitions(): array
    {
        return [
            PendingState::class,
        ];
    }

    public static function color(): string
    {
        return '#5e667f';
    }
}
