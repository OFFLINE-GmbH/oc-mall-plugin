<?php

namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Encryptable;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Payments\PaymentProvider;
use Session;

class PaymentGatewaySettings extends Model
{
    use Encryptable;

    protected $encryptable = [];

    public $implement = ['System.Behaviors.SettingsModel'];
    public $settingsCode = 'offline_mall_settings';
    public $settingsFields = '$/offline/mall/models/settings/fields_payment_gateways.yaml';

    /**
     * Extend the setting form with input fields for each
     * registered plugin.
     */
    public function getFieldConfig()
    {
        if ($this->fieldConfig !== null) {
            return $this->fieldConfig;
        }

        $config                 = parent::getFieldConfig();
        $config->tabs['fields'] = [];

        /** @var PaymentGateway $gateway */
        $gateway = app(PaymentGateway::class);
        collect($gateway->getProviders())->each(function ($providerClass) use ($config) {
            /** @var PaymentProvider $provider */
            $provider = new $providerClass();
            $settings = $this->setDefaultTab($provider->settings(), $provider->name());

            $config->tabs['fields'] = array_merge($config->tabs['fields'], $settings);
            $this->encryptable      = array_merge($this->encryptable, $provider->encryptedSettings());
        });

        return $config;
    }

    protected function setDefaultTab(array $settings, $tab)
    {
        return array_map(function ($i) use ($tab) {
            if ( ! isset($i['tab'])) {
                $i['tab'] = $tab;
            }

            return $i;
        }, $settings);
    }
}
