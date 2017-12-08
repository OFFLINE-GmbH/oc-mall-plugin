<?php

namespace OFFLINE\Mall\Classes\PaymentStatus;


class FailedState extends PaymentStatus
{
    public function toPending()
    {

    }

    public function toRefunded()
    {

    }

    public function toPaid()
    {

    }

    public function getAvailableTransitions(): array
    {
        return [
            PendingState::class,
            RefundedState::class,
            PaidState::class,
        ];
    }
}