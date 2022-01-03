<?php

namespace OFFLINE\Mall\Classes\Cart;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\Totals\TotalsCalculatorInput;
use OFFLINE\Mall\Classes\Utils\Money;
use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\Discount;

class DiscountApplier
{
    /**
     * @var TotalsCalculatorInput
     */
    private $input;
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
    /**
     * @var Money
     */
    private $money;

    public function __construct(TotalsCalculatorInput $input, float $total, float $baseTotal = null)
    {
        $this->input        = $input;
        $this->total        = $total;
        $this->reducedTotal = $baseTotal ?? $total;
        $this->discounts    = collect([]);
        $this->money        = app(Money::class);
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

        if ($discount->type === 'shipping') {
            $this->reducedTotal        = $discount->shippingPrice()->integer;
            $savings                   = $this->input->shipping_method->price()->integer -
                $discount->shippingPrice()->integer;
            $this->reducedTotalIsFixed = true;
        }

        if ($discount->type === 'fixed_amount') {
            $savings            = $this->calculateSaving($discount);
            $this->reducedTotal -= $savings;
        }

        if ($discount->type === 'rate') {
            $savings            = $this->calculateSaving($discount);
            $this->reducedTotal -= $savings;
        }

        if($savings == 0) {
            return null;
        }
        
        $this->discounts->push([
            'discount'          => $discount,
            'savings'           => $savings * -1,
            'savings_formatted' => $this->money->format($savings * -1),
        ]);

        return true;
    }

    protected function calculateSaving(Discount $discount): float {
        $total = $this->total;
        $quantity = 1;

        if($discount->products->isNotEmpty()) {
            $total = 0;
            $quantity = 0;
            foreach($this->input->products as $cartProduct) {
                if(in_array($cartProduct->product_id, $discount->products->pluck('product_id')->toArray())) {
                    $total += $cartProduct->price()->integer * $cartProduct->quantity;
                    $quantity += $cartProduct->quantity;
                }
            }
        }

        if($discount->variants->isNotEmpty()) {
            $total = 0;
            $quantity = 0;
            foreach($this->input->products as $cartProduct) {
                if(in_array($cartProduct->variant_id, $discount->variants->pluck('variant_id')->toArray())) {
                    $total += $cartProduct->price()->integer * $cartProduct->quantity;
                    $quantity += $cartProduct->quantity;
                }
            }
        }

        if($discount->categories->isNotEmpty()) {
            $total = 0;
            $quantity = 0;
            foreach($this->input->products as $cartProduct) {
                foreach($cartProduct->product->categories as $category) {
                    if(in_array($category->id, $discount->categories->pluck('id')->toArray())) {
                        $total += $cartProduct->price()->integer * $cartProduct->quantity;
                        $quantity += $cartProduct->quantity;
                        break;
                    }
                }
            }
        }

        if($discount->type == 'rate') return $total * ($discount->rate / 100);
        if($discount->type == 'fixed_amount') return $discount->amount()->integer * $quantity;
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

        if ($discount->trigger === 'total' && (int)$discount->totalToReach()->integer <= $this->total) {
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

    private function productIsInCart(int $productId): bool
    {
        return $this->input->products->pluck('product_id')->contains($productId);
    }

    private function userBelongsToCustomerGroup(int $customerGroupId): bool
    {
        $group = optional(\Auth::getUser())->customer_group();
        if ( ! $group) {
            return false;
        }
        return $group->where('id', $customerGroupId)->exists();
    }

    private function appliesForShippingMethod(Discount $discount): bool
    {
        return $discount->shipping_methods->contains($this->input->shipping_method->id);
    }

    private function checkPaymentMethod(int $method_id) {
        if(isset($this->input->payment_method)) {
            return $method_id == $this->input->payment_method->id;
        }
    }

}
