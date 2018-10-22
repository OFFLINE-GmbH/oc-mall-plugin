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
}
