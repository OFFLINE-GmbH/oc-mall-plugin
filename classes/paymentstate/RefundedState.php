<?php

namespace OFFLINE\Mall\Classes\PaymentState;

class RefundedState extends PaymentState
{
    public function getAvailableTransitions(): array
    {
        return [
            PendingState::class,
        ];
    }
}
