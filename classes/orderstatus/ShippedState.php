<?php

namespace OFFLINE\Mall\Classes\OrderStatus;


class ShippedState extends OrderStatus
{
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
            DeliveredState::class,
            PendingState::class,
            CancelledState::class,
            ProcessedState::class,
        ];
    }
}