<?php


namespace OFFLINE\Mall\Classes\Traits;


use October\Rain\Support\Collection;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Tax;
use RainLab\User\Facades\Auth;

use Event;

/**
 * This trait is used to filter a Collection of taxes based
 * on a provided shipping destination country.
 */

trait FilteredTaxes
{
    public $countryId;

    /**
     * Filter a tax collection based on the shipping destination country.
     *
     * @param $taxes
     *
     * @return Collection
     */
    public function getFilteredTaxes($taxes)
    {
        if (!$taxes instanceof Collection) {
            $taxes = Collection::wrap($taxes);
        }

        // Don't filter anything and don't use the default tax if no taxes were passed in.
        if ($taxes->count() === 0) {
            return $taxes;
        }

        $this->countryId = $this->getCartCountryId();

        Event::fire('mall.cart.setCountry', $this);

        // If the shipping destination is not yet known, return the default tax.
        if ($this->countryId === null) {
            return Tax::defaultTaxes();
        }

        // If the shipping destination is known, return all taxes that have
        // no country attached (valid for all countries) and all taxes that have
        // the shipping country attached.
        return $taxes->filter(function ($tax) {
            return $tax->countries->count() === 0 || $tax->countries->pluck('id')->search($this->countryId) !== false;
        });
    }

    /**
     * Return the current shipping destination country id.
     * If the destination is currently unknown, null is returned.
     */
    public function getCartCountryId()
    {
        $cart = Cart::byUser(Auth::getUser());
        if (!$cart) {
            return null;
        } else {
            return optional($cart->shipping_address)->country_id ?? $cart->getFallbackShippingCountryId();
        }
    }
}
