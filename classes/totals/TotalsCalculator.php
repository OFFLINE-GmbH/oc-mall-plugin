<?php

namespace OFFLINE\Mall\Classes\Totals;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use October\Contracts\Twig\CallsAnyMethod;
use OFFLINE\Mall\Classes\Cart\DiscountApplier;
use OFFLINE\Mall\Classes\Traits\FilteredTaxes;
use OFFLINE\Mall\Classes\Traits\Rounding;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\Tax;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class TotalsCalculator implements CallsAnyMethod
{
    use FilteredTaxes, Rounding;

    /**
     * @var Collection
     */
    public $shippingTaxes;

    /**
     * @var Collection
     */
    public $paymentTaxes;

    /**
     * @var TotalsCalculatorInput
     */
    protected $input;

    /**
     * @var Collection<TaxTotal>
     */
    protected $taxes;

    /**
     * @var Collection<TaxTotal>
     */
    protected $detailedTaxes;

    /**
     * @var ShippingTotal
     */
    protected $shippingTotal;

    /**
     * @var int
     */
    protected $weightTotal;

    /**
     * @var int
     */
    protected $totalPreTaxes;

    /**
     * @var int
     */
    protected $totalPostTaxes;

    /**
     * @var int
     */
    protected $totalTaxes;

    /**
     * @var int
     */
    protected $totalDiscounts;

    /**
     * @var int
     */
    protected $productPreTaxes;

    /**
     * @var int
     */
    protected $productTaxes;

    /**
     * @var int
     */
    protected $productPostTaxes;

    /**
     * @var Collection
     */
    protected $appliedDiscounts;

    /**
     * @var Collection
     */
    protected $productLineTotals;

    /**
     * @var float
     */
    protected $productPostTaxesOriginal;

    /**
     * @var int
     */
    protected $totalPrePayment;

    /**
     * @var PaymentTotal
     */
    protected $paymentTotal;

    public function __construct(TotalsCalculatorInput $input)
    {
        $this->input = $input;
        $this->taxes = new Collection();
        $this->productLineTotals = new Collection();

        $this->calculate();
    }

    public function paymentTotal(): PaymentTotal
    {
        return $this->paymentTotal;
    }

    public function shippingTotal(): ShippingTotal
    {
        return $this->shippingTotal;
    }

    public function weightTotal(): int
    {
        return $this->weightTotal;
    }

    public function totalPreTaxes(): float
    {
        return $this->totalPreTaxes;
    }

    public function totalTaxes(): float
    {
        return $this->totalTaxes;
    }
    
    public function totalDiscounts(): float
    {
        return $this->totalDiscounts;
    }
    
    public function totalPrePayment(): float
    {
        return $this->totalPrePayment;
    }

    public function productPreTaxes(): float
    {
        return $this->productPreTaxes;
    }

    public function productTaxes(): float
    {
        return $this->productTaxes;
    }

    public function productPostTaxes(): float
    {
        return $this->productPostTaxes;
    }

    public function totalPostTaxes(): float
    {
        return $this->totalPostTaxes;
    }

    public function taxes(bool $detailed = false): Collection
    {
        return $detailed ? $this->detailedTaxes : $this->taxes;
    }

    public function detailedTaxes(): Collection
    {
        return $this->taxes(true);
    }

    public function getInput(): TotalsCalculatorInput
    {
        return $this->input;
    }

    public function appliedDiscounts(): Collection
    {
        return $this->appliedDiscounts;
    }

    /**
     * Return the current shipping destination country id.
     */
    public function getCartCountryId()
    {
        return $this->input->shipping_country_id;
    }

    protected function calculate()
    {
        $this->weightTotal = $this->calculateWeightTotal();

        $this->productLineTotals = $this->buildProductLineTotals();

        $this->productPreTaxes = $this->productLineTotals->sum('pre_total');
        $productTaxTotals = $this->makeProductTaxTotals();
        $this->productTaxes = $this->round($productTaxTotals->sum(fn (TaxTotal $tax) => $tax->total()));
        $this->productPostTaxes = $this->productPreTaxes + $this->productTaxes;
        $this->totalDiscounts = max(0, $this->productPostTaxesOriginal - $this->productPostTaxes);

        $this->shippingTaxes = $this->filterShippingTaxes();
        $this->shippingTotal = new ShippingTotal($this->input->shipping_method, $this);
        $this->totalPreTaxes = $this->productPreTaxes + $this->shippingTotal->totalPreTaxes();

        $this->totalPrePayment = $this->productPostTaxes + $this->shippingTotal->totalPostTaxes();
        $this->paymentTaxes = $this->filterPaymentTaxes();
        $this->paymentTotal = new PaymentTotal($this->input->payment_method, $this);

        $this->totalPostTaxes = $this->totalPrePayment + $this->paymentTotal->totalPostTaxes();

        if ($this->totalPostTaxes < 0) {
            $this->totalPostTaxes = 0;
        }

        $this->taxes = $this->getTaxTotals();
    }

    protected function buildProductLineTotals(): Collection
    {
        $products = $this->input->products->values();

        $basePostTotal = max(0, $products->sum('totalPostTaxes'));
        $this->productPostTaxesOriginal = $basePostTotal;

        $discountedTotal = $this->applyTotalDiscounts($basePostTotal) ?? $basePostTotal;

        if ($this->appliedDiscounts === null) {
            $this->appliedDiscounts = new Collection();
        }

        $totalDiscount = max(0, $basePostTotal - $discountedTotal);
        $totalDiscount = min($totalDiscount, $basePostTotal);

        $remainingDiscount = $totalDiscount;
        $remainingPost = $basePostTotal;
        $count = $products->count();

        return $products->map(function ($product, $index) use (&$remainingDiscount, &$remainingPost, $count) {
            return $this->buildLineTotalsForProduct($product, $remainingDiscount, $remainingPost, $count, $index);
        });
    }

    protected function buildLineTotalsForProduct($product, float &$remainingDiscount, float &$remainingPost, int $count, int $index): array
    {
        $originalProductPre = max(0, $product->totalProductPreTaxes);
        $originalProductTax = max(0, $product->totalProductTaxes);
        $originalProductPost = max(0, $product->totalProductPostTaxes);

        $originalServicePre = max(0, $product->totalServicePreTaxes);
        $originalServiceTax = max(0, $product->totalServiceTaxes);
        $originalServicePost = max(0, $product->totalServicePostTaxes);

        $originalPreTotal = $originalProductPre + $originalServicePre;
        $originalTaxTotal = $originalProductTax + $originalServiceTax;
        $originalPostTotal = $originalPreTotal + $originalTaxTotal;

        $lineDiscountPost = 0.0;

        if ($originalPostTotal > 0 && $remainingDiscount > 0) {
            $isLastRelevantLine = ($index === $count - 1) || ($remainingPost - $originalPostTotal <= 0.0000001);
            if ($isLastRelevantLine || $remainingPost <= 0) {
                $lineDiscountPost = min($originalPostTotal, $remainingDiscount);
            } else {
                $ratio = $remainingPost > 0 ? $originalPostTotal / $remainingPost : 0;
                $lineDiscountPost = min($originalPostTotal, $remainingDiscount * $ratio);
            }

            $lineDiscountPost = max(0, $lineDiscountPost);
            $remainingDiscount -= $lineDiscountPost;

            if ($remainingDiscount < 0) {
                $lineDiscountPost += $remainingDiscount;
                $remainingDiscount = 0;
            }
        }

        $remainingPost -= $originalPostTotal;
        if ($remainingPost < 0) {
            $remainingPost = 0;
        }

        $productShare = $originalPostTotal > 0 ? $originalProductPost / $originalPostTotal : 0;
        $productDiscountPost = min($originalProductPost, $lineDiscountPost * $productShare);

        $remainingForService = max(0, $lineDiscountPost - $productDiscountPost);
        $serviceDiscountPost = min($originalServicePost, $remainingForService);

        $allocatedPost = $productDiscountPost + $serviceDiscountPost;
        if ($allocatedPost < $lineDiscountPost && $lineDiscountPost > 0) {
            $remainder = $lineDiscountPost - $allocatedPost;

            if ($productDiscountPost < $originalProductPost) {
                $extra = min($remainder, $originalProductPost - $productDiscountPost);
                $productDiscountPost += $extra;
                $remainder -= $extra;
            }

            if ($remainder > 0 && $serviceDiscountPost < $originalServicePost) {
                $extra = min($remainder, $originalServicePost - $serviceDiscountPost);
                $serviceDiscountPost += $extra;
                $remainder -= $extra;
            }
        }

        $productPreShare = $originalProductPost > 0 ? $originalProductPre / $originalProductPost : 0;
        $productDiscountPre = min($originalProductPre, $productDiscountPost * $productPreShare);
        $productDiscountTax = min($originalProductTax, max(0, $productDiscountPost - $productDiscountPre));

        $servicePreShare = $originalServicePost > 0 ? $originalServicePre / $originalServicePost : 0;
        $serviceDiscountPre = min($originalServicePre, $serviceDiscountPost * $servicePreShare);
        $serviceDiscountTax = min($originalServiceTax, max(0, $serviceDiscountPost - $serviceDiscountPre));

        $discountedProductPre = max(0, $originalProductPre - $productDiscountPre);
        $discountedServicePre = max(0, $originalServicePre - $serviceDiscountPre);
        $discountedProductTax = max(0, $originalProductTax - $productDiscountTax);
        $discountedServiceTax = max(0, $originalServiceTax - $serviceDiscountTax);

        $preTotal = $discountedProductPre + $discountedServicePre;
        $taxTotal = $discountedProductTax + $discountedServiceTax;

        return [
            'product' => $product,
            'product_pre' => $discountedProductPre,
            'service_pre' => $discountedServicePre,
            'product_tax' => $discountedProductTax,
            'service_tax' => $discountedServiceTax,
            'pre_total' => $preTotal,
            'tax_total' => $taxTotal,
            'post_total' => $preTotal + $taxTotal,
            'discount_post' => $lineDiscountPost,
            'service_pre_original' => $originalServicePre,
        ];
    }

    protected function makeProductTaxTotals(): Collection
    {
        return $this->productLineTotals->flatMap(function (array $line) {
            $productTaxes = $line['product']->filtered_product_taxes->map(function (Tax $tax) use ($line) {
                return new TaxTotal($line['product_pre'], $tax);
            });

            $servicePreOriginal = $line['service_pre_original'];
            $serviceScale = $servicePreOriginal > 0 ? $line['service_pre'] / $servicePreOriginal : 0;
            $serviceScale = max(0, min(1, $serviceScale));

            $serviceTaxes = $line['product']->filtered_service_taxes->map(function (TaxTotal $taxTotal) use ($serviceScale) {
                $preTax = $taxTotal->preTax() * $serviceScale;

                return new TaxTotal($preTax, $taxTotal->tax);
            });

            return $productTaxes->concat($serviceTaxes);
        });
    }

    protected function getTaxTotals(): Collection
    {
        $shippingTaxes = new Collection();
        $shippingTotal = $this->shippingTotal->totalPreTaxesOriginal();

        if ($this->shippingTaxes) {
            $shippingTaxes = $this->shippingTaxes->map(fn (Tax $tax) => new TaxTotal($shippingTotal, $tax));
        }

        $paymentTaxes = new Collection();
        $paymentTotal = $this->paymentTotal->totalPreTaxesOriginal();

        if ($this->paymentTaxes) {
            $paymentTaxes = $this->paymentTaxes->map(fn (Tax $tax) => new TaxTotal($paymentTotal, $tax));
        }

        $productTaxes = $this->makeProductTaxTotals();

        $combined = $productTaxes->concat($shippingTaxes)->concat($paymentTaxes);

        $this->totalTaxes = $combined->sum(fn (TaxTotal $tax) => $tax->total());
        $this->detailedTaxes = $combined;

        return $this->consolidateTaxes($combined);
    }

    /**
     * This method consolidates the same taxes on shipping
     * and products down to one combined TaxTotal.
     */
    protected function consolidateTaxes(Collection $taxTotals)
    {
        return $taxTotals->groupBy(fn (TaxTotal $taxTotal) => $taxTotal->tax->id)->map(function (Collection $grouped) {
            $tax = $grouped->first()->tax;

            $preTax = $grouped->sum(fn (TaxTotal $tax) => $tax->preTax());

            $taxTotal = new TaxTotal($preTax, $tax);

            $taxTotal->setTotal($grouped->sum(fn (TaxTotal $type) => (new TaxTotal($type->preTax(), $tax))->total()));

            return $taxTotal;
        })->values();
    }

    protected function calculateWeightTotal(): int
    {
        return $this->input->products->sum(fn ($product) => $product->weight * $product->quantity);
    }

    /**
     * Process the discounts that are applied to the cart's total.
     * @param mixed $total
     */
    protected function applyTotalDiscounts($total): ?float
    {
        $nonCodeTriggers = Discount::whereIn('trigger', ['total', 'product', 'customer_group', 'shipping_method', 'payment_method'])
            ->with('shipping_methods')
            ->where(function ($q) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', Carbon::now());
            })->where(function ($q) {
                $q->whereNull('expires')
                    ->orWhere('expires', '>', Carbon::now());
            })->get();

        $discounts = $this->input->discounts->merge($nonCodeTriggers)->reject(fn ($item) => $item->type === 'shipping');

        $applier = new DiscountApplier($this->input, $total);
        $this->appliedDiscounts = $applier->applyMany($discounts);

        return $applier->reducedTotal();
    }

    /**
     * Filter out shipping taxes that have to be applied for
     * the current shipping address of the cart.
     *
     * @return Collection
     */
    protected function filterShippingTaxes()
    {
        $taxes = optional($this->input->shipping_method)->taxes ?? new Collection();

        return $this->getFilteredTaxes($taxes, ignoreDefaults: true);
    }

    /**
     * Filter out payment taxes that have to be applied for
     * the current shipping address of the cart.
     *
     * @return Collection
     */
    protected function filterPaymentTaxes()
    {
        $taxes = optional($this->input->payment_method)->taxes ?? new Collection();

        return $this->getFilteredTaxes($taxes, ignoreDefaults: true);
    }
}
