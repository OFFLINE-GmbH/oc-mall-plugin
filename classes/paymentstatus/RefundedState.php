<?php

namespace OFFLINE\Mall\Classes\PaymentStatus;

class RefundedState extends PaymentStatus
{
    public function toPending()
    {
    }

    public function getAvailableTransitions(): array
    {
        return [
            PendingState::class,
        ];
    }
}
