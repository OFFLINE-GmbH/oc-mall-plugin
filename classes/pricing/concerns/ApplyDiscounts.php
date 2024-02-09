<?php declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Concerns;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use October\Rain\Database\Model;
use OFFLINE\Mall\Classes\Pricing\PriceBag;
use OFFLINE\Mall\Classes\Pricing\Values\AmountValue;
use OFFLINE\Mall\Classes\Pricing\Values\DiscountValue;
use OFFLINE\Mall\Classes\Pricing\Values\FactorValue;
use OFFLINE\Mall\Models\Discount;
use Whitecube\Price\Price;

/**
 * This trait takes over the applying and reverting of all global-added discounts of the respective
 * PriceBag instance.
 */
trait ApplyDiscounts
{

    /**
     * Switch if discounts has been applied or not.
     * @var boolean
     */
    protected bool $discountsApplied = false;

    /**
     * Apply Discounts
     * @return void
     */
    public function applyDiscounts()
    {
        if ($this->discountsApplied) {
            return;
        }
        $this->discountsApplied = true;

        // Calculate Products
        $total = $this->productsExclusive()->exclusive()->getMinorAmount()->toInt();
        $products = [];
        foreach ($this->map['products'] AS $product) {
            $products[] = intval(round((100 / $total) * $product->exclusive()->integer()));
        }

        // Apply Discounts
        foreach ($this->map['discounts'] AS $discount) {
            $amount = $discount->amount();

            // Check if discount is applicable
            $model = $discount->model();
            if ($model instanceof Discount && !$this->discountApplicable($model)) {
                return;
            }

            // Product Discounts
            if ($discount->type() == 'products') {
                if ($amount instanceof AmountValue) {
                    if (count($products) == 1) {
                        $this->map['products'][0]->addDiscount(
                            $amount,
                            false,
                            false
                        );
                        continue;
                    }
                    
                    $value = clone $amount->base();
                    $values = $value->allocate(...$products);
                    for ($i = 0; $i < count($values); $i++) {
                        $this->map['products'][$i]->addDiscount(
                            $values[$i]->getMinorAmount()->toInt(),
                            false,
                            false
                        );
                    }
                } else if ($amount instanceof FactorValue) {
                    foreach ($this->map['products'] AS $product) {
                        $product->addDiscount(
                            $amount,
                            false,
                            false
                        );
                    }
                }
            }

            // Shipping Discounts
            if ($discount->type() == 'shipping') {
                if ($amount instanceof AmountValue) {
                    foreach ($this->map['shipping'] AS $shipping) {
                        $shipping->setAmount($amount->value(), $discount->model());
                    }
                }
            }
        }
    }

    /**
     * Check if discount is applicable.
     * @todo Refactor / Create a better, more extendable solution.
     * @param Discount $discount
     * @return boolean
     */
    protected function discountApplicable(Discount $discount): bool
    {
        if ($discount->max_number_of_usages !== null && $discount->max_number_of_usages < $discount->number_of_usages) {
            return false;
        }

        if ($discount->trigger === 'total' && (int) $discount->totalToReach()->integer <= $this->productsInclusive()->inclusive()->getMinorAmount()->toInt()) {
            return true;
        }

        if ($discount->trigger === 'product' && $this->productIsInCart($discount->product_id)) {
            return true;
        }

        if ($discount->trigger === 'customer_group' && $this->userBelongsToCustomerGroup($discount->customer_group_id)) {
            return true;
        }
        
        if ($discount->trigger === 'shipping_method' && $this->appliesForShippingMethod($discount)) {
            return true;
        }

        if ($discount->trigger === 'payment_method' && $this->checkPaymentMethod($discount->payment_method_id)) {
            return true;
        }

        return $discount->trigger === 'code';
    }

    /**
     * Check if product is in stack.
     * @todo Refactor / Create a better, more extendable solution.
     * @param integer $id
     * @return boolean
     */
    protected function productIsInCart(int $id): bool
    {
        foreach ($this->map['products'] AS $product) {
            if (($product->model()->id ?? 0) === $id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user belongs to customer group.
     * @todo Refactor / Create a better, more extendable solution.
     * @param integer $id
     * @return boolean
     */
    protected function userBelongsToCustomerGroup(int $id): bool
    {
        /** @ignore @disregard facade alias for \RainLab\User\Classes\AuthManager */
        $group = optional(Auth::getUser())->customer_group();
        return $group ? $group->where('id', $id)->exists() : false;
    }

    /**
     * Check if shipping method fits.
     * @todo Refactor / Create a better, more extendable solution.
     * @param Discount $discount
     * @return boolean
     */
    protected function appliesForShippingMethod(Discount $discount): bool
    {
        return $discount->shipping_methods->contains($this->map['shipping'][0]->model()->id ?? 0);
    }

    /**
     * Check if payment method fits.
     * @todo Refactor / Create a better, more extendable solution.
     * @param integer $id
     * @return boolean
     */
    protected function checkPaymentMethod(int $id): bool
    {
        return ($this->map['payment'][0]->model()->id ?? 0) == $id;
    }

    /**
     * Revert Discounts
     * @return void
     */
    public function revertDiscounts()
    {

    }

}
