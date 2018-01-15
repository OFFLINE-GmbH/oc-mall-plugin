<?php

namespace OFFLINE\Mall\Classes\OrderStatus;

class DeliveredState extends OrderStatus
{
    public function toShipped()
    {
    }

    public function toDisputed()
    {
    }

    public function toPending()
    {
    }

    public function toCancelled()
    {
    }

    public function toProcessed()
    {
    }

    public function getAvailableTransitions(): array
    {
        return [
            DisputedState::class,
            ShippedState::class,
            PendingState::class,
            CancelledState::class,
            ProcessedState::class,
        ];
    }
}
