<?php

namespace OFFLINE\Mall\Classes\Payments;


use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentMethod;
use Session;

class DefaultPaymentGateway implements PaymentGateway
{
    public $providers = [];

    public function registerProvider(PaymentProvider $provider)
    {
        $this->providers[$provider->identifier()] = get_class($provider);
    }

    public function getProviderById(string $identifier): PaymentProvider
    {
        if ( ! isset($this->providers[$identifier])) {
            throw new \InvalidArgumentException(sprintf('Payment provider %s is not registered.', $identifier));
        }

        return $this->providers[$identifier];
    }

    public function process(Order $order, array $data): PaymentResult
    {
        $provider = $this->getProviderForMethod($order->payment_method);
        $provider->setOrder($order);
        $provider->setData($data);

        $provider->validate();

        Session::put('oc-mall.payment.id', str_random(8));

        return $provider->process();
    }

    protected function getProviderForMethod(PaymentMethod $method): PaymentProvider
    {
        if (isset($this->providers[$method->payment_provider])) {
            return new $this->providers[$method->payment_provider];
        }

        throw new \LogicException(
            sprintf('The selected payment provider "%s" is unavailable.', $method->payment_provider)
        );
    }

    public function getProviders(): array
    {
        return $this->providers;
    }
}