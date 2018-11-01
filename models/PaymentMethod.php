<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Nullable;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Traits\PriceAccessors;
use System\Models\File;

class PaymentMethod extends Model
{
    use Sluggable;
    use SoftDelete;
    use Sortable;
    use Validation;
    use PriceAccessors;
    use Nullable;

    const MORPH_KEY = 'mall.payment_method';

    public $rules = [
        'name'             => 'required',
        'payment_provider' => 'required',
        'fee_percentage'   => 'nullable|max:99',
    ];
    public $table = 'offline_mall_payment_methods';
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $appends = ['settings'];
    public $with = ['prices'];
    public $nullable = ['fee_percentage'];
    public $hidden = ['settings', 'prices'];
    public $slugs = [
        'code' => 'name',
    ];
    public $casts = [
        'fee_percentage' => 'float',
    ];
    public $translatable = [
        'name',
        'description',
    ];
    public $hasMany = [
        'orders' => Order::class,
    ];
    public $attachOne = [
        'logo' => File::class,
    ];
    public $morphMany = [
        'prices' => [
            Price::class,
            'name'       => 'priceable',
            'conditions' => 'price_category_id is null and field is null',
        ],
    ];
    public $belongsToMany = [
        'taxes' => [
            Tax::class,
            'table'    => 'offline_mall_payment_method_tax',
            'key'      => 'payment_method_id',
            'otherKey' => 'tax_id',
        ],
    ];

    public function afterDelete()
    {
        \DB::table('offline_mall_prices')
           ->where('priceable_type', self::MORPH_KEY)
           ->where('priceable_id', $this->id)
           ->delete();
    }

    public function getPaymentProviderOptions(): array
    {
        /** @var PaymentGateway $gateway */
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
        return static::orderBy('sort_order', 'ASC')->first();
    }

    public function getSettingsAttribute()
    {
        /** @var PaymentGateway $gateway */
        $gateway  = app(PaymentGateway::class);
        $provider = $gateway->getProviderById($this->payment_provider);

        return $provider->getSettings();
    }
}
