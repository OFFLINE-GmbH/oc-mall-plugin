<?php

namespace OFFLINE\Mall\Classes\OrderStatus;


class DisputedState extends OrderStatus
{
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
            ShippedState::class,
            DeliveredState::class,
            PendingState::class,
            CancelledState::class,
            ProcessedState::class,
        ];
    }
}