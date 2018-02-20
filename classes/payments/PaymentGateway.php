<?php


namespace OFFLINE\Mall\Classes\Payments;

use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Order;

interface PaymentGateway
{
    public function registerProvider(PaymentProvider $provider);

    public function getProviderById(string $identifier): PaymentProvider;

    public function getProviders(): array;

    public function init(Cart $cart, array $data);

    public function process(Order $order): PaymentResult;
}
