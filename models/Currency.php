<?php namespace OFFLINE\Mall\Models;

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
    ];
    public $table = 'offline_mall_currencies';

    public function beforeSave()
    {
        // Enforce a single default currency.
        if ($this->is_default) {
            DB::table($this->table)->where('id', '<>', $this->id)->update(['is_default' => false]);
        }
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
            if ( ! $currency) {
                throw new RuntimeException(
                    '[mall] Please configure at least one currency via the backend settings.'
                );
            }

            static::setActiveCurrency($currency);
        }

        return (new Currency)->newFromBuilder(Session::get(static::CURRENCY_SESSION_KEY));
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
}
