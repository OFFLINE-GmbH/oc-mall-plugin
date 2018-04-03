<?php

namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Encryptable;
use Session;

class PaymentGatewaySettings extends Model
{
    use Encryptable;

    protected $encryptable = ['stripe_api_key', 'paypal_client_id', 'paypal_secret'];

    public $implement = ['System.Behaviors.SettingsModel'];
    public $settingsCode = 'offline_mall_settings';
    public $settingsFields = '$/offline/mall/models/settings/fields_payment_gateways.yaml';
}
