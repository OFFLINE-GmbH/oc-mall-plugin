<?php

namespace OFFLINE\Mall\Classes\Payments;

use Cms\Classes\Theme;
use October\Rain\Exception\ValidationException;
use October\Rain\Parse\Twig;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\PaymentGatewaySettings;
use Request;
use Session;
use Url;

/**
 * A PaymentProvider handles the integration with external
 * payment providers.
 */
abstract class PaymentProvider
{
    /**
     * The order that is being paid.
     *
     * @var Order
     */
    public $order;
    /**
     * Data that is needed for the payment.
     *
     * @var array
     */
    public $data;

    /**
     * Return the display name of this payment provider.
     *
     * @return string
     */
    abstract public function name(): string;

    /**
     * Return a unique identifier for this payment provider.
     *
     * @return string
     */
    abstract public function identifier(): string;

    /**
     * Return any custom backend settings fields.
     *
     * @return array
     */
    abstract public function settings(): array;

    /**
     * Validate the given input data for this payment.
     *
     * @return bool
     * @throws ValidationException
     */
    abstract public function validate(): bool;

    /**
     * Process the payment.
     *
     * @param PaymentResult $result
     *
     * @return PaymentResult
     */
    abstract public function process(PaymentResult $result): PaymentResult;

    /**
     * PaymentProvider constructor.
     *
     * Optionally pass an order or payment data.
     *
     * @param Order|null $order
     * @param array      $data
     */
    public function __construct(Order $order = null, array $data = [])
    {
        if ($order) {
            $this->setOrder($order);
        }
        if ($data) {
            $this->setData($data);
        }
    }

    /**
     * Fields returned from this method are stored encrypted.
     *
     * Use this to store API tokens and other secret data
     * that is needed for this PaymentProvider to work.
     *
     * @return array
     */
    public function encryptedSettings(): array
    {
        return [];
    }

    /**
     * Name of the payment form partial.
     *
     * @return string
     */
    public function paymentFormPartial(): string
    {
        return 'form';
    }

    /**
     * Name of the customer methods partial.
     *
     * @return string
     */
    public function customerMethodsPartial(): string
    {
        return 'customermethods';
    }

    /**
     * Code of the active theme.
     *
     * @return string
     */
    public function activeThemeCode(): string
    {
        return Theme::getActiveThemeCode();
    }

    /**
     * Renders the payment form partial.
     *
     * @param Cart|Order $cartOrOrder
     *
     * @return string
     */
    public function renderPaymentForm($cartOrOrder): string
    {
        $override = themes_path(sprintf('%s/partials/mall/payments/%s/%s.htm',
            $this->activeThemeCode(),
            $this->identifier(),
            $this->paymentFormPartial()
        ));

        if (file_exists($override)) {
            return (new Twig)->parse(file_get_contents($override), ['cart' => $cartOrOrder]);
        }

        $fallback = plugins_path(sprintf(
            'offline/mall/classes/payments/%s/%s.htm',
            $this->identifier(),
            $this->paymentFormPartial()
        ));

        return file_exists($fallback)
            ? (new Twig)->parse(file_get_contents($fallback), ['cart' => $cartOrOrder])
            : '';
    }

    /**
     * Set the order that is being paid.
     *
     * @param null|Order
     *
     * @return PaymentProvider
     */
    public function setOrder(?Order $order)
    {
        $this->order = $order;
        Session::put('mall.payment.order', optional($this->order)->id);

        return $this;
    }

    /**
     * Set the data for this payment.
     *
     * @param array $data
     *
     * @return PaymentProvider
     */
    public function setData(array $data)
    {
        $this->data = $data;
        Session::put('mall.payment.data', $data);

        return $this;
    }

    /**
     * Get the settings of this PaymentProvider.
     *
     * @return \October\Rain\Support\Collection
     */
    public function getSettings()
    {
        return collect($this->settings())->mapWithKeys(function ($settings, $key) {
            return [$key => PaymentGatewaySettings::get($key)];
        });
    }

    /**
     * Get an order that was stored in the session.
     *
     * This is used to get the current order back into memory after the
     * user has been redirected to an external payment service.
     *
     * @return Order
     */
    public function getOrderFromSession(): Order
    {
        $id = Session::pull('mall.payment.order');

        return Order::findOrFail($id);
    }

    /**
     * Return URL passed to external payment services.
     *
     * The user will be redirected back to this URL once the external
     * payment service has done its work.
     *
     * @return string
     */
    public function returnUrl(): string
    {
        return Request::url() . '?' . http_build_query([
                'return'             => 'return',
                'oc-mall_payment_id' => $this->getPaymentId(),
            ]);
    }

    /**
     * Cancel URL passed to external payment services.
     *
     * The user will be redirected back to this URL if she cancels
     * the payment on an external payment service.
     *
     * @return string
     */
    public function cancelUrl(): string
    {
        return Request::url() . '?' . http_build_query([
                'return'             => 'cancel',
                'oc-mall_payment_id' => $this->getPaymentId(),
            ]);
    }

    /**
     * Get this payment's id form the session.
     *
     * @return string
     */
    private function getPaymentId()
    {
        return Session::get('mall.payment.id');
    }
}
