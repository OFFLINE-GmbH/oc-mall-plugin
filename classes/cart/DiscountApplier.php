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
        $this->discounts    = [];
    }

    public function apply(Discount $discount)
    {
        if ( ! $this->discountCanBeApplied($discount)) {
            return false;
        }

        if ($this->reducedTotalIsFixed === true) {
            return true;
        }

        if ($discount->type === 'alternate_price') {
            $this->reducedTotal        = $discount->getOriginal('alternate_price');
            $this->reducedTotalIsFixed = true;
        }

        if ($discount->type === 'shipping') {
            $this->reducedTotal        = $discount->getOriginal('shipping_price');
            $this->reducedTotalIsFixed = true;
        }

        if ($discount->type === 'fixed_amount') {
            $this->reducedTotal -= $discount->amount;
        }

        if ($discount->type === 'rate') {
            $this->reducedTotal -= $this->total * ($discount->rate / 100);
        }

        $this->discounts[] = $discount;

        return true;
    }

    public function applyMany(Collection $discounts)
    {
        foreach ($discounts as $discount) {
            if ( ! $this->apply($discount)) {
                return false;
            }
        }

        return true;
    }

    public function reducedTotal(): float
    {
        return $this->reducedTotal;
    }

    protected function discountCanBeApplied(Discount $discount): bool
    {
        if ($discount->trigger === 'total' && (int)$discount->getOriginal('total_to_reach') <= $this->total) {
            return true;
        }
        if ($discount->trigger === 'product' && $this->productIsInCart($discount->product_id)) {
            return true;
        }

        return $discount->trigger === 'code';
    }

    private function productIsInCart(int $product_id): bool
    {
        return $this->cart->products->pluck('product_id')->contains($product_id);
    }
}
