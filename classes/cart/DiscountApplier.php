<?php

namespace OFFLINE\Mall\Classes\Cart;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Discount;

class DiscountApplier
{
    /**
     * @var Cart
     */
    private $cart;
    /**
     * @var int
     */
    private $total;
    /**
     * @var int
     */
    private $reducedTotal;
    /**
     * @var Discount[]
     */
    private $discounts;
    /**
     * @var bool
     */
    private $reducedTotalIsFixed = false;

    public function __construct(Cart $cart, float $total, float $baseTotal = null)
    {
        $this->cart         = $cart;
        $this->total        = $total;
        $this->reducedTotal = $baseTotal ?? $total;
        $this->discounts    = collect([]);
    }

    public function apply(Discount $discount): ?bool
    {
        if ( ! $this->discountCanBeApplied($discount)) {
            return null;
        }

        if ($this->reducedTotalIsFixed === true) {
            return false;
        }

        $savings = 0;
        if ($discount->type === 'alternate_price') {
            $this->reducedTotal        = $discount->alternatePriceInCurrencyInteger();
            $this->reducedTotalIsFixed = true;
            $savings                   = $this->total - $discount->alternatePriceInCurrencyInteger();
        }

        if ($discount->type === 'shipping') {
            $this->reducedTotal        = $discount->shippingPriceInCurrencyInteger();
            $savings                   = $this->cart->shipping_method->priceInCurrencyInteger() -
                $discount->shippingPriceInCurrencyInteger();
            $this->reducedTotalIsFixed = true;
        }

        if ($discount->type === 'fixed_amount') {
            $savings            = $discount->amountInCurrencyInteger();
            $this->reducedTotal -= $savings;
        }

        if ($discount->type === 'rate') {
            $savings            = $this->total * ($discount->rate / 100);
            $this->reducedTotal -= $savings;
        }

        $this->discounts->push([
            'discount'          => $discount,
            'savings'           => $savings * -1,
            'savings_formatted' => format_money($savings * -1),
        ]);

        return true;
    }

    public function applyMany(Collection $discounts): Collection
    {
        foreach ($discounts as $discount) {
            // A return value of `false` indicates that a discount is applied that
            // fixes the final amount so no other discounts would have an effect.
            if ($this->apply($discount) === false) {
                break;
            }
        }

        return $this->discounts;
    }

    public function reducedTotal(): ?float
    {
        return $this->reducedTotal;
    }

    protected function discountCanBeApplied(Discount $discount): bool
    {
        if ($discount->max_number_of_usages !== null && $discount->max_number_of_usages < $discount->number_of_usages) {
            return false;
        }

        if ($discount->trigger === 'total' && (int)$discount->totalToReachInCurrencyInteger() <= $this->total) {
            return true;
        }

        if ($discount->trigger === 'product' && $this->productIsInCart($discount->product_id)) {
            return true;
        }

        return $discount->trigger === 'code';
    }

    private function productIsInCart(int $productId): bool
    {
        return $this->cart->products->pluck('product_id')->contains($productId);
    }
}
