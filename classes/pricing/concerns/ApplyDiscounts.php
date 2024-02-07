<?php declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Concerns;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use OFFLINE\Mall\Classes\Pricing\Values\AmountValue;
use OFFLINE\Mall\Classes\Pricing\Values\DiscountValue;
use OFFLINE\Mall\Classes\Pricing\Values\FactorValue;
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
        }
    }

    /**
     * Revert Discounts
     * @return void
     */
    public function revertDiscounts()
    {

    }

}
