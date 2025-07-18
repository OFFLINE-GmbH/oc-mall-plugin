<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Cart;

use Auth;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\Totals\TotalsCalculatorInput;
use OFFLINE\Mall\Classes\Utils\Money;
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

    public function __construct(TotalsCalculatorInput $input, float $total, ?float $baseTotal = null)
    {
        $this->input        = $input;
        $this->total        = $total;
        $this->reducedTotal = $baseTotal ?? $total;
        $this->discounts    = collect([]);
        $this->money        = app(Money::class);
    }

    public function apply(Discount $discount): ?bool
    {
        if (! $this->discountCanBeApplied($discount)) {
            return null;
        }

        if ($this->reducedTotalIsFixed === true) {
            return false;
        }

        $savings = 0;

        if ($discount->type === 'shipping') {
            $this->reducedTotal        = $discount->shippingPrice()->integer;
            $savings = $this->input->shipping_method->price()->integer - $discount->shippingPrice()->integer;
            $this->reducedTotalIsFixed = true;
        }

        if ($discount->type === 'fixed_amount') {
            $savings            = $discount->amount()->integer;
            $this->reducedTotal -= $savings;
        }

        if ($discount->type === 'rate') {
            $savings            = $this->total * ($discount->rate / 100);
            $this->reducedTotal -= $savings;
        }

        $this->discounts->push([
            'discount'          => $discount,
            'savings'           => $savings * -1,
            'savings_formatted' => $this->money->format($savings * -1),
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

    public function discountCanBeApplied(Discount $discount): bool
    {
        if ($discount->max_number_of_usages !== null && $discount->number_of_usages >= $discount->max_number_of_usages) {
            return false;
        }

        if ($discount->trigger === 'total' && (int)$discount->totalToReach()->integer <= $this->total) {
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

    private function productIsInCart(int $productId): bool
    {
        return $this->input->products->pluck('product_id')->contains($productId);
    }

    private function userBelongsToCustomerGroup(int $customerGroupId): bool
    {
        $group = optional(Auth::user())->customer_group();

        if (! $group) {
            return false;
        }

        return $group->where('id', $customerGroupId)->exists();
    }

    private function appliesForShippingMethod(Discount $discount): bool
    {
        return $discount->shipping_methods->contains($this->input->shipping_method->id);
    }

    private function checkPaymentMethod(int $method_id)
    {
        if (isset($this->input->payment_method)) {
            return $method_id == $this->input->payment_method->id;
        }
    }
}
