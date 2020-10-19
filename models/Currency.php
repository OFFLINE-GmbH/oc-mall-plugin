<?php namespace OFFLINE\Mall\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Model;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use RuntimeException;
use Session;

class Currency extends Model
{
    use Validation;
    use Sortable;

    public const CURRENCY_SESSION_KEY = 'mall.currency.active';
    public const CURRENCIES_CACHE_KEY = 'mall.currencies';
    public const DEFAULT_CURRENCY_CACHE_KEY = 'mall.currency.default';

    public static $defaultCurrency;

    public $rules = [
        'code'     => 'required|unique:offline_mall_currencies,code',
        'rate'     => 'required',
        'decimals' => 'required',
        'format'   => 'required',
    ];
    public $fillable = [
        'code',
        'symbol',
        'rate',
        'decimals',
        'format',
        'is_default',
    ];
    public $casts = [
        'is_default' => 'boolean',
        'rate'       => 'float',
    ];
    public $table = 'offline_mall_currencies';

    public function getRoundingOptions()
    {
        return [
            1 => '0.01',
            5 => '0.05',
            10 => '0.10',
            50 => '0.50',
            100 => '1.00',
        ];
    }

    public function beforeSave()
    {
        // Enforce a single default currency.
        if ($this->is_default) {
            DB::table($this->table)->where('id', '<>', $this->id)->update(['is_default' => false]);
        }
    }

    public function afterSave()
    {
        Cache::forget(self::DEFAULT_CURRENCY_CACHE_KEY);
        Cache::forget(self::CURRENCIES_CACHE_KEY);
        Session::forget(static::CURRENCY_SESSION_KEY);
    }

    public function afterDelete()
    {
        DB::table('offline_mall_prices')->where('currency_id', $this->id)->delete();
        DB::table('offline_mall_product_prices')->where('currency_id', $this->id)->delete();
        DB::table('offline_mall_customer_group_prices')->where('currency_id', $this->id)->delete();
        Cache::forget(self::CURRENCIES_CACHE_KEY);
        Cache::forget(self::DEFAULT_CURRENCY_CACHE_KEY);
        Session::forget(static::CURRENCY_SESSION_KEY);
    }

    /**
     * Returns the currently active currency from the session.
     * @return Currency
     * @throws \RuntimeException
     */
    public static function activeCurrency()
    {
        if ( ! Session::has(static::CURRENCY_SESSION_KEY)) {
            $currency = static::orderBy('is_default', 'DESC')->first();
            static::guardMissingCurrency($currency);
            static::setActiveCurrency($currency);
        }

        return (new Currency)->newFromBuilder(Session::get(static::CURRENCY_SESSION_KEY));
    }

    /**
     * Returns the default currency.
     *
     * @return Currency
     */
    public static function defaultCurrency()
    {
        if (static::$defaultCurrency) {
            return static::$defaultCurrency;
        }

        $currency = Cache::rememberForever(static::DEFAULT_CURRENCY_CACHE_KEY, function () {
            $currency = static::orderBy('is_default', 'DESC')->first();
            static::guardMissingCurrency($currency);

            return $currency->toArray();
        });

        static::$defaultCurrency = (new Currency)->newFromBuilder($currency);

        return static::$defaultCurrency;
    }

    /**
     * Returns an array of all currency information.
     *
     * @return mixed
     */
    public static function getAll()
    {
        return Currency::hydrate(Cache::rememberForever(static::CURRENCIES_CACHE_KEY, function () {
            return Currency::get(['id', 'rate', 'symbol', 'code'])->keyBy('id')->toArray();
        }));
    }

    protected static function guardMissingCurrency($currency)
    {
        if ( ! $currency) {
            throw new RuntimeException(
                '[mall] Please configure at least one currency via the backend settings.'
            );
        }
    }

    /**
     * Sets the currently active currency in the session.
     *
     * @param Currency $currency
     *
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
