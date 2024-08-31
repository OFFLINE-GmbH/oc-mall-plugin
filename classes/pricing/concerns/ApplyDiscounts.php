<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Concerns;

use OFFLINE\Mall\Classes\Pricing\Values\FactorValue;
use OFFLINE\Mall\Classes\Pricing\Values\MoneyValue;
use OFFLINE\Mall\Models\Discount;

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

        foreach ($this->map['products'] as $product) {
            if ($total > 0) {
                $products[] = intval(round((100 / $total) * $product->exclusive()->toInt()));
            } else {
                $products[] = 0;
            }
        }

        // Apply Discounts
        foreach ($this->map['discounts'] as $discount) {
            $amount = $discount->amount();

            // Check if discount is applicable
            $model = $discount->model();

            if ($model instanceof Discount && !$this->discountApplicable($model)) {
                return;
            }

            // Product Discounts
            if ($discount->type() == 'products') {
                if ($amount instanceof MoneyValue) {
                    if (count($products) == 1) {
                        $this->map['products'][0]->addDiscount(
                            $amount,
                            false,
                            false
                        );

                        continue;
                    }
                    
                    $value = $amount->value();
                    $values = $value->allocate(...$products);
                    for ($i = 0; $i < count($values); $i++) {
                        $this->map['products'][$i]->addDiscount(
                            $values[$i]->getMinorAmount()->toInt(),
                            false,
                            false
                        );
                    }
                } elseif ($amount instanceof FactorValue) {
                    foreach ($this->map['products'] as $product) {
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
                if ($amount instanceof MoneyValue) {
                    foreach ($this->map['shipping'] as $shipping) {
                        $shipping->setAmount($amount->price(), $discount->model());
                    }
                }
            }
        }
    }

    /**
     * Revert Discounts
     * @return void
     */
    public function revertDiscounts()
    {
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

        if ($discount->trigger === 'product' && $this->productIsInCart(intval($discount->product_id))) {
            return true;
        }

        if ($discount->trigger === 'customer_group' && $this->userBelongsToCustomerGroup(intval($discount->customer_group_id))) {
            return true;
        }
        
        if ($discount->trigger === 'shipping_method' && $this->appliesForShippingMethod($discount)) {
            return true;
        }

        if ($discount->trigger === 'payment_method' && $this->checkPaymentMethod(intval($discount->payment_method_id))) {
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
        foreach ($this->map['products'] as $product) {
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
        $group = optional(Auth::user())->customer_group();

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
}
