<?php


namespace OFFLINE\Mall\Classes\Payments;

use OFFLINE\Mall\Models\Order;

interface PaymentGateway
{
    public function registerProvider(PaymentProvider $provider);

    public function getProviderById(string $identifier): PaymentProvider;

    public function getProviders(): array;

    public function process(Order $order, array $data): PaymentResult;
}
