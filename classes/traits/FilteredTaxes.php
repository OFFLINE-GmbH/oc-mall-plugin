<?php


namespace OFFLINE\Mall\Classes\Traits;


use October\Rain\Support\Collection;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Tax;
use RainLab\User\Facades\Auth;

trait FilteredTaxes
{
    public function getCartCountryId()
    {
        $cart = Cart::byUser(Auth::getUser());
        if ( ! $cart) {
            return null;
        }

        return optional($cart->shipping_address)->country_id;
    }

    public function getFilteredTaxes($taxes)
    {
        if ( ! $taxes instanceof Collection) {
            $taxes = Collection::wrap($taxes);
        }
        $countryId = $this->getCartCountryId();


        if ($countryId === null) {
            return Collection::wrap(Tax::defaultTax());
        }

        return $this->cachedFilteredTaxes = $taxes->filter(function ($tax) use ($countryId) {
            return $tax->countries->count() === 0 || $tax->countries->pluck('id')->search($countryId) !== false;
        });
    }
}