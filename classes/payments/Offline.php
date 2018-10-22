<?php

namespace OFFLINE\Mall\Classes\Payments;

/**
 * This provider can be used for all offline payments.
 *
 * The order's payment state will be marked as pending if
 * this provider is used.
 */
class Offline extends PaymentProvider
{
    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return 'Offline';
    }

    /**
     * {@inheritdoc}
     */
    public function identifier(): string
    {
        return 'offline';
    }

    /**
     * {@inheritDoc}
     */
    public function validate(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function process(PaymentResult $result): PaymentResult
    {
        return $result->pending();
    }

    /**
     * {@inheritdoc}
     */
    public function settings(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function encryptedSettings(): array
    {
        return [];
    }
}
