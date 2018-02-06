<?php

namespace OFFLINE\Mall\Classes\PaymentState;

class FailedState extends PaymentState
{
    public function getAvailableTransitions(): array
    {
        return [
            PendingState::class,
            RefundedState::class,
            PaidState::class,
        ];
    }
}
