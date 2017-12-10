<?php

namespace OFFLINE\Mall\Models;

use Model;
use RuntimeException;
use Session;

class CurrencySettings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];
    public $settingsCode = 'offline_mall_settings';
    public $settingsFields = '$/offline/mall/models/settings/fields_currency.yaml';
    const CURRENCY_SESSION_KEY = 'mall.activeCurrency';

    /**
     * Returns all supported currency codes.
     */
    public static function currencies()
    {
        return collect(self::get('currencies'))->pluck('code', 'code');
    }

    /**
     * Returns the formats for all currencies.
     */
    public static function currencyFormats()
    {
        return collect(self::get('currencies'))->pluck('format', 'code');
    }

    /**
     * Returns the currently active currency from the session.
     * @return string
     * @throws \RuntimeException
     */
    public static function activeCurrency()
    {
        if ( ! Session::has(static::CURRENCY_SESSION_KEY)) {
            $currencies = static::currencies();
            if ($currencies->count() < 1) {
                throw new RuntimeException(
                    '[mall] Please configure at least one currency via the backend settings.'
                );
            }

            static::setActiveCurrency($currencies->first());
        }

        return Session::get(static::CURRENCY_SESSION_KEY);
    }

    /**
     * Returns the format for the currently active currency.
     * @return string
     * @throws \RuntimeException
     */
    public static function activeCurrencyFormat()
    {
        $currency = CurrencySettings::activeCurrency();
        $formats  = CurrencySettings::currencyFormats();

        $format  = $formats->get($currency, false);
        $default = "{{ currency }} {{ price|number_format(2, '.', '\'') }}";

        return $format ? $format : $default;
    }

    /**
     * Sets the currently active currency in the session.
     * @return string
     */
    public static function setActiveCurrency($currency)
    {
        return Session::put(static::CURRENCY_SESSION_KEY, $currency);
    }

}