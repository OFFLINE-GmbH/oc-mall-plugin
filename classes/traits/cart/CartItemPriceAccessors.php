<?php

namespace OFFLINE\Mall\Classes\Traits\Cart;


use October\Rain\Support\Collection;
use OFFLINE\Mall\Classes\Totals\TaxTotal;
use OFFLINE\Mall\Models\ServiceOption;
use OFFLINE\Mall\Models\Tax;

trait CartItemPriceAccessors
{
    /**
     * Cached tax factor.
     * @var int
     */
    protected $productTaxFactor;

    /**
     * The total item price * quantity pre taxes, including all services.
     */
    public function getTotalPreTaxesAttribute(): float
    {
        return $this->totalProductPreTaxes + $this->totalServicePreTaxes;
    }

    /**
     * The tax total for this cart entry, including all services.
     */
    public function getTotalTaxesAttribute(): float
    {
        return $this->totalProductTaxes + $this->totalServiceTaxes;
    }

    /**
     * The total item price * quantity post taxes, including all services.
     */
    public function getTotalPostTaxesAttribute(): float
    {
        return $this->totalProductPostTaxes + $this->totalServicePostTaxes;
    }

    public function getTotalWeightAttribute(): float
    {
        return $this->weight * $this->quantity;
    }

    /**
     * The price of a single product, without taxes.
     */
    public function getProductPreTaxesAttribute()
    {
        if ($this->data->price_includes_tax) {
            return $this->price()->integer / (1 + $this->productTaxFactor());
        }

        return $this->price()->integer;
    }

    /**
     * The taxes of a single product.
     */
    public function getProductTaxesAttribute()
    {
        return $this->productPostTaxes - $this->productPreTaxes;
    }

    /**
     * The price of a single product, with taxes.
     */
    public function getProductPostTaxesAttribute()
    {
        if ($this->data->price_includes_tax) {
            return $this->price()->integer;
        }

        return $this->price()->integer * (1 + $this->productTaxFactor());
    }

    /**
     * The price of all products, without taxes.
     */
    public function getTotalProductPreTaxesAttribute()
    {
        return $this->productPreTaxes * $this->quantity;
    }

    /**
     * The taxes of all products.
     */
    public function getTotalProductTaxesAttribute()
    {
        return $this->productTaxes * $this->quantity;
    }

    /**
     * The price of all products, with taxes.
     */
    public function getTotalProductPostTaxesAttribute()
    {
        return $this->productPostTaxes * $this->quantity;
    }

    /**
     * The price of a single product, including its services.
     */
    public function getPricePreTaxesAttribute()
    {
        if ($this->data->price_includes_tax) {
            return $this->price()->integer / (1 + $this->productTaxFactor()) + $this->servicePreTaxes;
        }

        return $this->price()->integer + $this->servicePreTaxes;
    }

    /**
     * Taxes of a single product including its services.
     */
    public function getTaxesAttribute()
    {
        if ($this->data->price_includes_tax) {
            return $this->pricePostTaxes - $this->pricePreTaxes;
        }

        return $this->productTaxes + $this->serviceTaxes;
    }

    /**
     * Price of a single product including its services.
     */
    public function getPricePostTaxesAttribute()
    {
        if ($this->data->price_includes_tax) {
            return $this->price()->integer + $this->totalServicePostTaxes;
        }

        return $this->pricePreTaxes + $this->taxes;
    }

    /**
     * The price of a single service, without taxes.
     */
    public function getServicePreTaxesAttribute()
    {
        return $this->servicePostTaxes - $this->serviceTaxes;
    }

    /**
     * The taxes of a single service.
     */
    public function getServiceTaxesAttribute()
    {
        return $this->service_options->sum(function ($option) {
            $taxes     = $option->service->taxes->filter($this->filterTaxes());
            $taxFactor = $taxes->sum('percentageDecimal');

            return $option->price()->integer / (1 + $taxFactor) * $taxFactor;
        });
    }

    /**
     * The cost of a single service, with taxes.
     */
    public function getServicePostTaxesAttribute()
    {
        return $this->service_options->sum(function ($option) {
            return $option->price()->integer;
        });
    }

    /**
     * The price of a all services, without taxes.
     */
    public function getTotalServicePreTaxesAttribute()
    {
        return $this->servicePreTaxes * $this->quantity;
    }

    /**
     * The taxes of a all services.
     */
    public function getTotalServiceTaxesAttribute()
    {
        return $this->serviceTaxes * $this->quantity;
    }

    /**
     * The cost of all services, with taxes.
     */
    public function getTotalServicePostTaxesAttribute()
    {
        return $this->servicePostTaxes * $this->quantity;
    }

    /**
     * Sum of all tax factors.
     * @return mixed
     */
    protected function productTaxFactor()
    {
        if ($this->productTaxFactor) {
            return $this->productTaxFactor;
        }

        return $this->productTaxFactor = $this->filtered_product_taxes->sum('percentageDecimal');
    }

    /**
     * Filter product taxes by shipping destination.
     *
     * @param array $data
     *
     * @return Collection
     */
    public function getFilteredProductTaxesAttribute()
    {
        $taxes = optional($this->data)->taxes ?? new Collection();

        return $taxes->filter($this->filterTaxes());
    }

    /**
     * Filter service taxes by shipping destination.
     *
     * @param array $data
     *
     * @return Collection
     */
    public function getFilteredServiceTaxesAttribute()
    {
        return $this->service_options->flatMap(function (ServiceOption $option) {
            $taxes    = $option->service->taxes->filter($this->filterTaxes());
            $factor   = $taxes->sum('percentageDecimal');
            $preTaxes = $option->price()->integer / (1 + $factor) * $this->quantity;

            return $taxes->map(function (Tax $tax) use ($preTaxes) {
                return new TaxTotal($preTaxes, $tax);
            });
        });
    }

    /**
     * Filter taxes based on shipping country restrictions.
     * @return \Closure
     */
    protected function filterTaxes()
    {
        return function (Tax $tax) {
            // If no shipping address is available only include taxes that have no country restrictions.
            if ($this->cart->shipping_address === null) {
                return $tax->countries->count() === 0;
            }

            return $tax->countries->count() === 0
                || $tax->countries->pluck('id')->search($this->cart->shipping_address->country_id) !== false;
        };
    }
}