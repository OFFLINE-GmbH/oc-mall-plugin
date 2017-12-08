<?php

namespace OFFLINE\Mall\Classes\PaymentStatus;


class PendingState extends PaymentStatus
{
    public function toFailed()
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
            FailedState::class,
            RefundedState::class,
            PaidState::class,
        ];
    }
}