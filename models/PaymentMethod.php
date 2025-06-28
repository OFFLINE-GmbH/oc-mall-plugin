<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Auth;
use Cms\Classes\Theme;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Model;
use October\Rain\Database\Traits\Nullable;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use October\Rain\Parse\Twig;
use October\Rain\Support\Facades\Event;
use OFFLINE\Mall\Classes\Database\IsStates;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Totals\PaymentTotal;
use OFFLINE\Mall\Classes\Traits\PriceAccessors;
use System\Models\File;

class PaymentMethod extends Model
{
    use IsStates;
    use Nullable;
    use PriceAccessors;
    use Sluggable;
    use SoftDelete;
    use Sortable;
    use Validation;

    /**
     * Morph key as used on the respective relationships.
     * @var string
     */
    public const MORPH_KEY = 'mall.payment_method';

    /**
     * Enable `is_default` handler on IsStates trait, by passing the column name.
     * @var null|string
     */
    public const IS_DEFAULT = null;

    /**
     * Enable `is_enabled` handler on IsStates trait, by passing the column name.
     * @var null|string
     */
    public const IS_ENABLED = 'is_enabled';

    /**
     * Implement behaviors for this model.
     * @var array
     */
    public $implement = [
        '@RainLab.Translate.Behaviors.TranslatableModel',
    ];

    /**
     * The table associated with this model.
     * @var string
     */
    public $table = 'offline_mall_payment_methods';

    /**
     * The translatable attributes of this model.
     * @var array
     */
    public $translatable = [
        'name',
        'description',
        'instructions',
    ];

    /**
     * The validation rules for the single attributes.
     * @var array
     */
    public $rules = [
        'name'              => 'required',
        'payment_provider'  => 'required',
        'fee_percentage'    => 'nullable',
        'is_enabled'        => 'nullable|boolean',
        'is_default'        => 'nullable|boolean',
    ];

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
    public $fillable = [
        'name',
        'code',
        'payment_provider',
        'fee_percentage',
        'is_enabled',
        'is_default',
    ];

    /**
     * Attributes which should be set to null, when empty.
     * @var array
     */
    public $nullable = [
        'fee_percentage',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    public $casts = [
        'is_enabled' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Automatically generate unique URL names for the passed attributes.
     * @var array
     */
    public $slugs = [
        'code' => 'name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var array<string>
     */
    public $hidden = [
        'settings',
        'prices',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
    /**
     * The accessors to append to the model's array form.
     * @var array
     */
    public $appends = [
        'settings',
    ];

    /**
     * The relations to eager load on every query.
     * @var array
     */
    public $with = [
        'prices',
    ];

    /**
     * The attachOne relationships of this model.
     * @var array
     */
    public $attachOne = [
        'logo' => File::class,
    ];

    /**
     * The belongsToMany relationships of this model.
     * @var array
     */
    public $belongsToMany = [
        'taxes' => [
            Tax::class,
            'table'    => 'offline_mall_payment_method_tax',
            'key'      => 'payment_method_id',
            'otherKey' => 'tax_id',
        ],
    ];

    /**
     * The hasMany relationships of this model.
     * @var array
     */
    public $hasMany = [
        'orders' => Order::class,
    ];

    /**
     * The morphMany relationships of this model.
     * @var array
     */
    public $morphMany = [
        'prices' => [
            Price::class,
            'name'       => 'priceable',
            'conditions' => 'price_category_id is null and field is null',
        ],
    ];

    /**
     * Get default payment method
     * @return null|self
     */
    public static function getDefault(): ?self
    {
        $default = static::where('is_default', 1)->first();

        if (empty($default)) {
            $default = static::orderBy('sort_order', 'ASC')->first();
        }

        return $default;
    }

    /**
     * Get all available payment methods.
     * @param Cart $cart
     * @return mixed
     */
    public static function getAvailableByCart(Cart $cart)
    {
        $results = array_filter(Event::fire('mall.cart.extendAvailablePaymentMethods', [$cart]) ?? []);

        if (count($results) > 0) {
            return $results[0];
        }

        return PaymentMethod::orderBy('sort_order', 'ASC')->get();
    }

    /**
     * Include all payment methods, even disabled ones.
     * @param Builder $query
     * @return void
     */
    public function scopeAll(Builder $query)
    {
        return $query->withDisabled();
    }

    /**
     * Hook after model has been deleted.
     * @return void
     */
    public function afterDelete()
    {
        DB::table('offline_mall_prices')
            ->where('priceable_type', self::MORPH_KEY)
            ->where('priceable_id', $this->id)
            ->delete();
    }

    /**
     * Renders the payment instructions.
     * @param null|Order $order
     * @param null|Cart $cart
     * @return null|string
     */
    public function renderInstructions(?Order $order = null, ?Cart $cart = null): ?string
    {
        if (!$this->instructions) {
            return null;
        } else {
            return (new Twig())->parse($this->instructions, [
                'order' => $order,
                'cart'  => $cart,
            ]);
        }
    }

    /**
     * Get payment provider options.
     * @return array
     */
    public function getPaymentProviderOptions(): array
    {
        /** @var PaymentGateway $gateway */
        $gateway = app(PaymentGateway::class);

        $options = [];

        foreach ($gateway->getProviders() as $id => $class) {
            $method       = new $class();
            $options[$id] = $method->name();
        }

        return $options;
    }

    /**
     * Get PDF partial options.
     * @return array
     */
    public function getPdfPartialOptions(): array
    {
        $null = [null => '-- ' . trans('offline.mall::lang.payment_method.pdf_partial_none')];
        $path = themes_path(sprintf('%s/partials/mallPDF/*', Theme::getActiveThemeCode()));

        return $null + collect(glob($path, GLOB_ONLYDIR))->mapWithKeys(
            fn ($dir) => [basename($dir) => basename($dir)]
        )->toArray();
    }

    /**
     * Get settings attribute.
     * @return mixed
     */
    public function getSettingsAttribute(): mixed
    {
        /** @var PaymentGateway $gateway */
        $gateway  = app(PaymentGateway::class);
        $provider = $gateway->getProviderById($this->payment_provider);

        return $provider->getSettings();
    }
    
    /**
     * Get price for the cart.
     * @return PaymentTotal
     */
    public function priceForCart(): PaymentTotal
    {
        /** @ignore @disregard facade alias for \RainLab\User\Classes\AuthManager */
        $user = Auth::user();
        $cart = Cart::byUser($user);

        return new PaymentTotal($this, $cart->totals);
    }
}
