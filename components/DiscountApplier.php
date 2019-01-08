<?php namespace OFFLINE\Mall\Components;

use Auth;
use October\Rain\Exception\ValidationException;
use October\Rain\Support\Facades\Flash;
use OFFLINE\Mall\Models\Cart;

/**
 * The DiscountApplier component allow the user to enter a discount code.
 */
class DiscountApplier extends MallComponent
{
    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.discountApplier.details.name',
            'description' => 'offline.mall::lang.components.discountApplier.details.description',
        ];
    }

    /**
     * Properties of this component.
     *
     * @return array
     */
    public function defineProperties()
    {
        return [];
    }

    /**
     * A discount code has been entered.
     *
     * Applies the discount code directly to the Cart model.
     *
     * @throws ValidationException
     */
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

        Flash::success(trans('offline.mall::lang.components.discountApplier.discount_applied'));
    }
}
