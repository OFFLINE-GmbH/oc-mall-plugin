<?php

namespace OFFLINE\Mall\Classes\Payments;

use Illuminate\Support\Facades\Session;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Models\CustomerPaymentMethod;
use OFFLINE\Mall\Models\PaymentGatewaySettings;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Omnipay;
use Omnipay\Stripe\Message\PaymentIntents\Response;
use Omnipay\Stripe\PaymentIntentsGateway;
use Throwable;
use Validator;

/**
 * Process the payment via Stripe.
 */
class Stripe extends PaymentProvider
{
    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return 'Stripe';
    }

    /**
     * {@inheritdoc}
     */
    public function identifier(): string
    {
        return 'stripe';
    }

    /**
     * {@inheritdoc}
     */
    public function validate(): bool
    {
        if (isset($this->data['use_customer_payment_method'])) {
            return true;
        }

        $rules = [
            'token' => 'required|size:28|regex:/tok_[0-9a-zA-z]{24}/',
        ];

        $validation = Validator::make($this->data, $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function process(PaymentResult $result): PaymentResult
    {
        $response                 = null;
        $useCustomerPaymentMethod = $this->order->customer_payment_method;
        try {
            $gateway = $this->getGateway();

            $customer        = $this->order->customer;
            $isFirstCheckout = false;

            // The checkout uses an existing payment method. The customer and
            // card references can be fetched from there.
            if ($useCustomerPaymentMethod) {
                $customerReference = $this->order->customer_payment_method->data['stripe_customer_id'];
                $cardReference     = $this->order->customer_payment_method->data['stripe_card_id'];
            } elseif ($customer->stripe_customer_id) {
                // If the customer uses a new payment method but is already registered
                // on Stripe, just create the new card.
                $response = $this->createCard($customer, $gateway);
                if ( ! $response->isSuccessful()) {
                    return $result->fail((array)$response->getData(), $response);
                }

                $customerReference = $customer->stripe_customer_id;
                $cardReference     = $response->getCardReference();

                $this->createCustomerPaymentMethod($customerReference, $cardReference, array_get($response->getData(), 'card'));
            } else {
                // If this is the first checkout for this customer we have to register
                // the customer and a card on Stripe.
                $response = $this->createCustomer($customer, $gateway);
                if ( ! $response->isSuccessful()) {
                    return $result->fail((array)$response->getData(), $response);
                }

                $responseData      = $response->getData();
                $customerReference = $response->getCustomerReference();
                $cardReference     = $response->getCardReference();

                // Try to find the newly created card in the response. Since this is a new customer,
                // the default_source will be the card we are looking for.
                $defaultSource = array_get($responseData, 'default_source');
                $sources = array_get($responseData, 'sources.data', []);
                $card = array_first($sources, function($source) use ($defaultSource) {
                    return $source['id'] === $defaultSource;
                });

                if ($card) {
                    $this->createCustomerPaymentMethod($customerReference, $cardReference, $card);
                }

                $isFirstCheckout = true;
            }

            if ($isFirstCheckout === false) {
                // Update the customer's data to reflect the order's data.
                $response = $this->updateCustomer($gateway, $customerReference, $customer);
                if ( ! $response->isSuccessful()) {
                    return $result->fail((array)$response->getData(), $response);
                }
            }

            $response = $this->charge($gateway, $customerReference, $cardReference);

        } catch (Throwable $e) {
            return $result->fail([], $e);
        }

        // Everthing went OK, no 3DS required.
        if ($response->isSuccessful()) {
            return $this->completeOrder($result, $response);
        }

        // 3DS authentication is required, redirect to Stripe.
        if ($response->isRedirect()) {
            Session::put('mall.payment.callback', self::class);
            Session::put('mall.stripe.paymentIntentReference', $response->getPaymentIntentReference());

            return $result->redirect($response->getRedirectUrl());
        }

        // Something went wrong! :(
        return $result->fail((array)$response->getData(), $response);
    }

    /**
     * Stripe has processed the payment and redirected the user back.
     *
     * @param PaymentResult $result
     *
     * @return PaymentResult
     */
    public function complete(PaymentResult $result): PaymentResult
    {
        $intentReference     = Session::pull('mall.stripe.paymentIntentReference');

        if ( ! $intentReference) {
            return $result->fail([
                'msg'   => 'Missing payment intent reference',
                'intent_reference'   => $intentReference,
            ], null);
        }

        $this->setOrder($result->order);

        try {
            $response = $this->getGateway()->confirm([
                'paymentIntentReference' => $intentReference,
                'returnUrl' => $this->returnUrl(),
            ])->send();
        } catch (Throwable $e) {
            return $result->fail([], $e);
        }

        if ( ! $response->isSuccessful()) {
            return $result->fail((array)$response->getData(), $response);
        }

        return $this->completeOrder($result, $response);
    }

    /**
     * Set the returned info from Stripe on the Order and Customer.
     *
     * @param PaymentResult $result
     * @param Response $response
     * @return PaymentResult
     */
    protected function completeOrder(PaymentResult $result, Response $response)
    {
        $data = $response->getData();

        $charge = array_get($data, 'charges.data.0', []);

        $this->order->card_type                = array_get($charge, 'payment_method_details.card.brand');
        $this->order->card_holder_name         = array_get($charge, 'payment_method_details.card.name');
        $this->order->credit_card_last4_digits = array_get($charge, 'payment_method_details.card.last4');

        $this->order->customer->stripe_customer_id = array_get($data, 'customer');
        $this->order->customer->save();

        return $result->success($data, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function settings(): array
    {
        return [
            'stripe_api_key'         => [
                'label'   => 'offline.mall::lang.payment_gateway_settings.stripe.api_key',
                'comment' => 'offline.mall::lang.payment_gateway_settings.stripe.api_key_comment',
                'span'    => 'left',
                'type'    => 'text',
            ],
            'stripe_publishable_key' => [
                'label'   => 'offline.mall::lang.payment_gateway_settings.stripe.publishable_key',
                'comment' => 'offline.mall::lang.payment_gateway_settings.stripe.publishable_key_comment',
                'span'    => 'left',
                'type'    => 'text',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function encryptedSettings(): array
    {
        return ['stripe_api_key'];
    }

    /**
     * Create a new customer.
     *
     * @param                  $customer
     * @param GatewayInterface $gateway
     *
     * @return mixed
     */
    protected function createCustomer($customer, GatewayInterface $gateway)
    {
        $description = sprintf(
            'OFFLINE.Mall Customer %s (%d)',
            $customer->user->email,
            $customer->id
        );

        return $gateway->createCustomer([
            'description' => $description,
            'source'      => $this->data['token'] ?? false,
            'email'       => $this->order->customer->user->email,
            'shipping'    => $this->getShippingInformation($customer),
            'metadata'    => [
                'name' => $customer->name,
            ],
        ])->send();
    }

    /**
     * Update the customer.
     *
     * @param GatewayInterface $gateway
     * @param                  $customerReference
     * @param                  $customer
     *
     * @return ResponseInterface
     */
    protected function updateCustomer(
        GatewayInterface $gateway,
        $customerReference,
        $customer
    ): ResponseInterface {
        return $gateway->updateCustomer([
            'customerReference' => $customerReference,
            'email'             => $this->order->customer->user->email,
            'shipping'          => $this->getShippingInformation($customer),
            'metadata'          => [
                'name' => $customer->name,
            ],
        ])->send();
    }

    /**
     * Create a new card.
     *
     * @param                  $customer
     * @param GatewayInterface $gateway
     *
     * @return mixed
     */
    protected function createCard($customer, GatewayInterface $gateway)
    {
        return $gateway->createCard([
            'customerReference' => $customer->stripe_customer_id,
            'token'             => $this->data['token'] ?? false,
            'name'              => $customer->name,
        ])->send();
    }

    /**
     * Get all available shipping information.
     *
     * @param $customer
     *
     * @return array
     */
    protected function getShippingInformation($customer): array
    {
        $name = $customer->shipping_address->name;
        if ($customer->shipping_address->company) {
            $name = sprintf(
                '%s (%s)',
                $customer->shipping_address->company,
                $customer->shipping_address->name
            );
        }

        return [
            'name'    => $name,
            'address' => [
                'line1'       => $customer->shipping_address->lines_array[0] ?? '',
                'line2'       => $customer->shipping_address->lines_array[1] ?? '',
                'city'        => $customer->shipping_address->city,
                'country'     => $customer->shipping_address->country->name,
                'postal_code' => $customer->shipping_address->zip,
                'state'       => optional($customer->shipping_address->state)->name,
            ],
        ];
    }

    /**
     * Charge the customer.
     *
     * @param GatewayInterface $gateway
     * @param                  $customerReference
     * @param                  $cardReference
     *
     * @return Response
     */
    protected function charge(GatewayInterface $gateway, $customerReference, $cardReference): Response
    {
        return $gateway->purchase([
            'amount'            => $this->order->total_in_currency,
            'currency'          => $this->order->currency['code'],
            'returnUrl'         => $this->returnUrl(),
            'cancelUrl'         => $this->cancelUrl(),
            'customerReference' => $customerReference,
            'cardReference'     => $cardReference,
            'confirm'           => true,
        ])->send();
    }

    /**
     * Create a CustomerPaymentMethod.
     *
     * @param       $customerReference
     * @param       $cardReference
     * @param array $card
     */
    protected function createCustomerPaymentMethod($customerReference, $cardReference, array $card)
    {
        CustomerPaymentMethod::create([
            'name'              => trans('offline.mall::lang.order.credit_card'),
            'customer_id'       => $this->order->customer->id,
            'payment_method_id' => $this->order->payment_method_id,
            'data'              => [
                'stripe_customer_id' => $customerReference,
                'stripe_card_id'     => $cardReference,
                'stripe_card_brand'  => array_get($card, 'brand'),
                'stripe_card_last4'  => array_get($card, 'last4'),
            ],
        ]);
    }

    /**
     * Build and return the Stripe Intents gateway.
     *
     * @return PaymentIntentsGateway
     */
    protected function getGateway(): PaymentIntentsGateway
    {
        $gateway = Omnipay::create('Stripe\PaymentIntents');
        $gateway->setApiKey(decrypt(PaymentGatewaySettings::get('stripe_api_key')));

        return $gateway;
    }

}
