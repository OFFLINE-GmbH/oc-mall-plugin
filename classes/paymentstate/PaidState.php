<?php

namespace OFFLINE\Mall\Classes\PaymentState;

class PaidState extends PaymentState
{
    public function getAvailableTransitions(): array
    {
        return [
            PendingState::class,
        ];
    }
}
