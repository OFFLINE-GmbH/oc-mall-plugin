<?php namespace OFFLINE\Mall\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Discount;

class DiscountApplier extends ComponentBase
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
