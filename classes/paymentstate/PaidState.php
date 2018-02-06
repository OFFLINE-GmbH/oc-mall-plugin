<?php

namespace OFFLINE\Mall\Classes\PaymentState;

class PaidState extends PaymentState
{
    public static function getAvailableTransitions(): array
    {
        return [
            PendingState::class,
        ];
    }
}
