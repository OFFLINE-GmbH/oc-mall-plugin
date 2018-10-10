<?php namespace OFFLINE\Mall\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Model;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use Session;

class Currency extends Model
{
    use Validation;
    use Sortable;

    public const CURRENCY_SESSION_KEY = 'mall.currency.active';
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
    }

    public function afterDelete()
    {
        DB::table('offline_mall_prices')->where('currency_id', $this->id)->delete();
        DB::table('offline_mall_product_prices')->where('currency_id', $this->id)->delete();
        DB::table('offline_mall_customer_group_prices')->where('currency_id', $this->id)->delete();
    }

    /**
     * Returns the currently active currency from the session.
     * @return array
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
     * Turns a currency id or code into a currency model.
     *
     * @param $currency
     *
     * @return mixed|string
     */
    protected static function resolve($currency = null)
    {
        if ($currency === null) {
            $currency = Currency::activeCurrency();
        }
        if ($currency instanceof Currency) {
            $currency = $currency;
        }
        if (is_string($currency)) {
            $currency = Currency::whereCode($currency)->firstOrFail();
        }
        if (is_int($currency)) {
            $currency = Currency::whereId($currency)->firstOrFail();
        }

        return $currency;
    }
}
