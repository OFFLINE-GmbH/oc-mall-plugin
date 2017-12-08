<?php

namespace OFFLINE\Mall\Classes\OrderStatus;

class InProgressState extends OrderStatus
{
    public function toDisputed()
    {

    }

    public function toShipped()
    {

    }

    public function toDelivered()
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
            DeliveredState::class,
            PendingState::class,
            CancelledState::class,
            ProcessedState::class,
        ];
    }
}