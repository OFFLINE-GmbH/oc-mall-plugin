<?php

namespace OFFLINE\Mall\Classes\Payments;

use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentMethod;

interface PaymentGateway
{
    public function registerProvider(PaymentProvider $provider);

    public function getProviderById(string $identifier): PaymentProvider;

    public function getProviders(): array;

    public function init(PaymentMethod $paymentMethod, array $data);

    public function process(Order $order): PaymentResult;
}
