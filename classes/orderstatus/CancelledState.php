<?php

namespace OFFLINE\Mall\Classes\OrderStatus;

class CancelledState extends OrderStatus
{
    public function toInProgress()
    {
    }

    public function getAvailableTransitions(): array
    {
        return [
            InProgressState::class
        ];
    }
}
