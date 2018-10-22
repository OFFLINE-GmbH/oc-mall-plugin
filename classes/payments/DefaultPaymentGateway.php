<?php

namespace OFFLINE\Mall\Classes\Payments;

use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentMethod;
use Session;

/**
 * The DefaultPaymentGateway is responsible for the orchestration
 * of all available payment providers.
 *
 * When a payment is being processed, the gateway sets up
 * all needed data to process this payment.
 */
class DefaultPaymentGateway implements PaymentGateway
{
    /**
     * The currently active PaymentProvider.
     * @var PaymentProvider
     */
    protected $provider;
    /**
     * An array of all registered PaymentProviders.
     * @var PaymentProvider[]
     */
    protected $providers = [];

    /**
     * {@inheritdoc}
     */
    public function registerProvider(PaymentProvider $provider): PaymentProvider
    {
        $this->providers[$provider->identifier()] = $provider;

        return $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderById(string $identifier): PaymentProvider
    {
        if ( ! isset($this->providers[$identifier])) {
            throw new \InvalidArgumentException(sprintf('Payment provider %s is not registered.', $identifier));
        }

        return $this->providers[$identifier];
    }

    /**
     * {@inheritdoc}
     */
    public function init(PaymentMethod $paymentMethod, array $data)
    {
        $this->provider = $this->getProviderForMethod($paymentMethod);
        $this->provider->setData($data);
        $this->provider->validate();
    }

    /**
     * {@inheritdoc}
     */
    public function process(Order $order): PaymentResult
    {
        if ( ! $this->provider) {
            throw new \LogicException('Missing data for payment. Make sure to call init() before process()');
        }

        Session::put('mall.payment.id', str_random(8));

        $this->provider->setOrder($order);
        $result = new PaymentResult($this->provider, $order);

        return $this->provider->process($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveProvider(): PaymentProvider
    {
        return $this->provider;
    }

    /**
     * Get the PaymentProvider that belongs to a PaymentMethod.
     *
     * @param PaymentMethod $method
     *
     * @return PaymentProvider
     */
    protected function getProviderForMethod(PaymentMethod $method): PaymentProvider
    {
        if (isset($this->providers[$method->payment_provider])) {
            return new $this->providers[$method->payment_provider];
        }

        throw new \LogicException(
            sprintf('The selected payment provider "%s" is unavailable.', $method->payment_provider)
        );
    }
}
