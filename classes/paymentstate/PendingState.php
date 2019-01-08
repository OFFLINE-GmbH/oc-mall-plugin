<?php

namespace OFFLINE\Mall\Classes\PaymentState;

class PendingState extends PaymentState
{
    public static function getAvailableTransitions(): array
    {
        return [
            FailedState::class,
            RefundedState::class,
            PaidState::class,
        ];
    }

    public static function color(): string
    {
        return '#3498db';
    }
}
