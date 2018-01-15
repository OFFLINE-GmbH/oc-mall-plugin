<?php

namespace OFFLINE\Mall\Classes\OrderStatus;

class PendingState extends OrderStatus
{
    public function toShipped()
    {
    }

    public function toDelivered()
    {
    }

    public function toDisputed()
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
            ShippedState::class,
            DeliveredState::class,
            CancelledState::class,
            ProcessedState::class,
        ];
    }
}
