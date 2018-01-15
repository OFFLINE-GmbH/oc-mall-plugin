<?php

namespace OFFLINE\Mall\Classes\OrderStatus;

class ProcessedState extends OrderStatus
{
    public function toInProgress()
    {
    }

    public function getAvailableTransitions(): array
    {
        return [
            InProgressState::class,
        ];
    }
}
