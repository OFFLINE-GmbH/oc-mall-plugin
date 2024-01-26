<?php declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Model;
use Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;

class Currency extends Model
{
    use Sortable;
    use Validation;

    public const CURRENCY_SESSION_KEY = 'mall.currency.active';
    public const CURRENCIES_CACHE_KEY = 'mall.currencies';
    public const DEFAULT_CURRENCY_CACHE_KEY = 'mall.currency.default';
    public const JSON_PRICE_CACHE_KEY = 'mall.jsonPrice.currencies';

    /**
     * The default set currency model.
     * @var Currency|null
     */
    public static Currency|null $defaultCurrency = null;

    /**
     * The available order state flag options.
     * @var array
     */
    public static $availableRoundingOptions = [
        1   => '0.01',
        5   => '0.05',
        10  => '0.10',
        50  => '0.50',
        100 => '1.00',
    ];

    /**
     * The table associated with this model.
     * @var string
     */
    public $table = 'offline_mall_currencies';

    /**
     * The validation rules for the single attributes.
     * @var array
     */
    public $rules = [
        'code'          => 'required|unique:offline_mall_currencies,code',
        'rate'          => 'required',
        'decimals'      => 'required',
        'format'        => 'required',
        'is_enabled'    => 'nullable|boolean'
    ];

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
    public $fillable = [
        'id',
        'code',
        'symbol',
        'rate',
        'decimals',
        'format',
        'is_default',
        'is_enabled'
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    public $casts = [
        'is_default' => 'boolean',
        'is_enabled' => 'boolean',
        'rate'       => 'float',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var array<string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get available order state flag options.
     * @return array
     */
    public function getRoundingOptions()
    {
        return self::$availableRoundingOptions;
    }

    /**
     * Hook before model is saved.
     * @return void
     */
    public function beforeSave()
    {
        // Disabled currencies cannot be the default ones.
        if ($this->is_enabled === false && $this->is_default) {
            $this->is_default = false;
        }

        // Enforce a single default currency.
        if ($this->is_default) {
            Currency::where('id', '<>', $this->id)->update(['is_default' => false]);
        }
        
        // Enforce one default currency.
        if (!$this->is_default && $this->isDirty('is_default')) {
            $count = Currency::where('id', '<>', $this->id)->where('is_default', true)->count();

            if ($count === 0) {
                $default = Currency::enabled()->where('id', '<>', $this->id)->first();
                if (empty($default)) {
                    throw new \Exception('No currency could be changed to the default currency.');
                } else {
                    $default->is_default = true;
                    $default->save();
                }
            }
        }
    }

    /**
     * Hook after model is saved.
     * @return void
     */
    public function afterSave()
    {
        Cache::forget(self::DEFAULT_CURRENCY_CACHE_KEY);
        Cache::forget(self::CURRENCIES_CACHE_KEY);
        Cache::forget(self::JSON_PRICE_CACHE_KEY);
        Session::forget(static::CURRENCY_SESSION_KEY);
    }

    /**
     * Hook after model is deleted.
     * @return void
     */
    public function afterDelete()
    {
        DB::table('offline_mall_prices')->where('currency_id', $this->id)->delete();
        DB::table('offline_mall_product_prices')->where('currency_id', $this->id)->delete();
        DB::table('offline_mall_customer_group_prices')->where('currency_id', $this->id)->delete();
        Cache::forget(self::CURRENCIES_CACHE_KEY);
        Cache::forget(self::DEFAULT_CURRENCY_CACHE_KEY);
        Cache::forget(self::JSON_PRICE_CACHE_KEY);
        Session::forget(static::CURRENCY_SESSION_KEY);
    }

    /**
     * Returns the currently active currency from the session.
     * @return Currency
     * @throws \RuntimeException
     */
    public static function activeCurrency()
    {
        if (!Session::has(static::CURRENCY_SESSION_KEY)) {
            $currency = static::enabled()->orderBy('is_default', 'DESC')->first();
            static::guardMissingCurrency($currency);
            static::setActiveCurrency($currency);
        } else {
            return (new Currency)->newFromBuilder(Session::get(static::CURRENCY_SESSION_KEY));
        }
    }

    /**
     * Returns the default currency.
     * @return Currency
     */
    public static function defaultCurrency()
    {
        if (static::$defaultCurrency) {
            return static::$defaultCurrency;
        }

        $currency = Cache::rememberForever(static::DEFAULT_CURRENCY_CACHE_KEY, function () {
            $currency = static::enabled()->orderBy('is_default', 'DESC')->first();
            static::guardMissingCurrency($currency);
            return $currency->toArray();
        });

        static::$defaultCurrency = (new Currency)->newFromBuilder($currency);
        return static::$defaultCurrency;
    }

    /**
     * Returns an array with all currency information.
     * @return mixed
     */
    public static function getAll()
    {
        return Currency::hydrate(Cache::rememberForever(static::CURRENCIES_CACHE_KEY, function () {
            return Currency::get(['id', 'rate', 'symbol', 'code'])->keyBy('id')->toArray();
        }));
    }

    /**
     * Additional guard when no currency is configured.
     *
     * @param mixed $currency
     * @return void
     * @throws \RuntimeException
     */
    protected static function guardMissingCurrency($currency)
    {
        if (!$currency) {
            throw new \RuntimeException(
                '[mall] Please configure at least one currency via the backend settings.'
            );
        }
    }

    /**
     * Custom scope to retrieve only enabled currencies.
     * @return mixed
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', 1);
    }

    /**
     * Sets the currently active currency in the session.
     * @param Currency $currency
     * @return string
     */
    public static function setActiveCurrency(Currency $currency)
    {
        return Session::put(static::CURRENCY_SESSION_KEY, $currency->toArray());
    }

    /**
     * Return a dummy currency for unknown inputs.
     * @return Currency
     */
    public static function unknown($currency = null): self
    {
        Log::error(
            '[OFFLINE.Mall] Unknown currency was requested',
            ['currency' => $currency, 'url' => request()->url()]
        );

        return new self([
            'code'       => '???',
            'symbol'     => trans('offline.mall::lang.currency_settings.unknown'),
            'rate'       => 1,
            'decimals'   => 2,
            'format'     => '{{ price|number_format(currency.decimals, " ", ",") }} ({{ currency.symbol }})',
            'is_default' => false,
        ]);
    }

    /**
     * Turns a currency id or code into a currency model.
     * @return Currency
     */
    public static function resolve($input = null): self
    {
        if ($input instanceof self) {
            return $input;
        }
        if ($input === null) {
            $currency = self::activeCurrency();
        }
        if (is_string($input)) {
            $currency = self::whereCode($input)->first();
        }
        if (is_int($input)) {
            $currency = self::whereId($input)->first();
        }
        if ( ! isset($currency) || ! $currency) {
            return self::unknown($input);
        }
        return $currency;
    }
}
