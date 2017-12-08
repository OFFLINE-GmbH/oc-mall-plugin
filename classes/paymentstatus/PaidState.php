<?php

namespace OFFLINE\Mall\Classes\PaymentStatus;


class PaidState extends PaymentStatus
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