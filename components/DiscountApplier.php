<?php namespace OFFLINE\Mall\Components;

use Auth;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Models\Cart;

class DiscountApplier extends MallComponent
{
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.discountApplier.details.name',
            'description' => 'offline.mall::lang.components.discountApplier.details.description',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onApplyDiscount()
    {
        $code = strtoupper(post('code'));
        $cart = Cart::byUser(Auth::getUser());

        try {
            $cart->applyDiscountByCode($code);
        } catch (\Throwable $e) {
            throw new ValidationException([
                'code' => $e->getMessage(),
            ]);
        }
    }
}
