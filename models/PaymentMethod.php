<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use System\Models\File;

class PaymentMethod extends Model
{
    use Validation;
    use SoftDelete;
    use Sortable;

    public $rules = [
        'name'             => 'required',
        'payment_provider' => 'required',
    ];

    public $table = 'offline_mall_payment_methods';

    public $hasMany = [
        'orders' => Order::class,
    ];

    public $attachOne = [
        'logo' => File::class,
    ];

    public function getPaymentProviderOptions(): array
    {
        $gateway = app(PaymentGateway::class);

        $options = [];
        foreach ($gateway->getProviders() as $id => $class) {
            $method       = new $class;
            $options[$id] = $method->name();
        }

        return $options;
    }

    public static function getDefault()
    {
        return static::first();
    }
}
