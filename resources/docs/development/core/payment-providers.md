# Payment providers

You can add your own payment provider by providing an implementation of a `PaymentProvider` class.
 
## Implement a PaymentProvider 

To implement a `PaymentProvider` simply extend the abstract `OFFLINE\Mall\Classes\Payments\PaymentProvider` class and
 implement all missing methods. 
 
You can place your custom `PaymentProvider` inside the `classes` directory of your plugin:

> plugins/yourname/yourplugin/classes/ExampleProvider.php
 
### PaymentResult

The `process` method receives an instance of a `PaymentResult`. You can call one of the three methods `success`, 
`redirect` and `fail` on it. This will automatically log the payment attempt and update the order. 

Check out [the payment providers that are shipped with the plugin](https://github.com/OFFLINE-GmbH/oc-mall-plugin/tree/develop/classes/payments) for more information on how to use these 
methods.

### Example implementation
 
 ```php
<?php
namespace YourName\YourPlugin\Classes;

use OFFLINE\Mall\Models\PaymentGatewaySettings;
use OFFLINE\Mall\Classes\Payments\PaymentResult;

class ExampleProvider extends \OFFLINE\Mall\Classes\Payments\PaymentProvider
{
    /**
     * The order that is being paid.
     *
     * @var \OFFLINE\Mall\Models\Order
     */
    public $order;
    /**
     * Data that is needed for the payment.
     * Card numbers, tokens, etc. 
     *
     * @var array
     */
    public $data;

    /**
     * Return the display name of your payment provider.
     *
     * @return string
     */
    public function name(): string
    {
        return 'Example provider';
    }

    /**
     * Return a unique identifier for this payment provider.
     *
     * @return string
     */
    public function identifier(): string
    {
        return 'example-provider';
    }

    /**
     * Validate the given input data for this payment.
     *
     * @return bool
     * @throws \October\Rain\Exception\ValidationException
     */
    public function validate(): bool
    {
        $rules = [
            'card_number' => 'required|size:16',
        ];

        $validation = \Validator::make($this->data, $rules);
        if ($validation->fails()) {
            throw new \October\Rain\Exception\ValidationException($validation);
        }

        return true;
    }
    
    /**
     * Return any custom backend settings fields.
     * 
     * These fields will be rendered in the backend
     * settings page of your provider. 
     *
     * @return array
     */
    public function settings(): array
    {
        return [
            'api_key'     => [
                'label'   => 'API-Key',
                'comment' => 'The API Key for the payment service',
                'span'    => 'left',
                'type'    => 'text',
            ],
        ];
    }
    
    /**
     * Setting keys returned from this method are stored encrypted.
     *
     * Use this to store API tokens and other secret data
     * that is needed for this PaymentProvider to work.
     *
     * @return array
     */
    public function encryptedSettings(): array
    {
        return ['api_key'];
    }

    /**
     * Process the payment.
     *
     * @param PaymentResult $result
     *
     * @return PaymentResult
     */
    public function process(PaymentResult $result): PaymentResult
    {
        $gateway = AnyPaymentService::create();
        $gateway->setApiKey(decrypt(PaymentGatewaySettings::get('api_key')));
        
        $response = null;
        try {
            $response = $gateway->purchase([
                'amount'    => $this->order->total_in_currency,
                'currency'  => $this->order->currency['code'],
                'token'     => $this->data['token'],
                'returnUrl' => $this->returnUrl(),
                'cancelUrl' => $this->cancelUrl(),
            ])->send();
        } catch (\Throwable $e) {
            // Something bad happened! Log the payment as failed.
            // There is no response data available so we pass an empty array.
            return $result->fail([], $e);
        }
        
        // Get the payment data from your gateway. 
        $data = (array)$response->getData();
        
        if ( ! $response->isSuccessful()) {
            // The response failed. Pass any payment data and the
            // response itself to the fail method to log this payment attempt.
            return $result->fail($data, $response);
        }
        
        // The payment was successful! You may update the order here.
        // You don't have to save it since the success method will take
        // care of it.
        //
        // $this->order->card_holder_name = $data['card_holder_name'];
        
        // Log the payment as successful.
        return $result->success($data, $response);
    }
}
```

## Register a PaymentProvider

To register a `PaymentProvider` with `oc-mall` simply call the `registerProvider` method from your own plugin's boot 
method.

```php
<?php
namespace YourName\YourPlugin;

use YourName\YourPlugin\Classes\ExampleProvider;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use System\Classes\PluginBase;

// Your Plugin.php
class Plugin extends PluginBase {

    public function boot()
    {
        $gateway = $this->app->get(PaymentGateway::class);
        $gateway->registerProvider(new ExampleProvider());
    }
    
}
```