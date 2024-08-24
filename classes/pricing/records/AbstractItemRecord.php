<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Records;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use OFFLINE\Mall\Classes\Exceptions\PriceBagException;
use OFFLINE\Mall\Classes\Pricing\BaseRecord;
use OFFLINE\Mall\Classes\Pricing\BaseValue;
use OFFLINE\Mall\Classes\Pricing\Concerns\InclusiveAccuracyFix;
use OFFLINE\Mall\Classes\Pricing\Values\DiscountValue;
use OFFLINE\Mall\Classes\Pricing\Values\FactorValue;
use OFFLINE\Mall\Classes\Pricing\Values\MoneyValue;
use OFFLINE\Mall\Classes\Pricing\Values\PriceValue;
use Whitecube\Price\Price;

/**
 * @internal Used to share methods between ProductRecord, ServiceRecord, ShippingRecord and
 * PaymentRecord only. Not used by DiscountRecord.
 */
abstract class AbstractItemRecord extends BaseRecord
{
    use InclusiveAccuracyFix;

    /**
     * The value-added tax (VAT) for this record.
     * @var null|FactorValue
     */
    protected ?FactorValue $vat = null;

    /**
     * The additional taxes for this record.
     * @var array<FactorValue|MoneyValue>
     */
    protected array $taxes = [ ];

    /**
     * The additional discounts for this record.
     * @var DiscountValue[]
     */
    protected array $discounts = [ ];

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'type'          => $this->type(),
            'exclusive'     => strval($this->exclusive()),
            'discounts'     => strval($this->discount()),
            'vat'           => strval($this->vat()),
            'taxes'         => strval($this->tax(true)),
            'inclusive'     => strval($this->inclusive()),
        ];
    }

    /**
     * Add discount to record.
     * You can pass factors (ex. 10.5, for 10.5% of the net-value) or a fixed amount (ex. '10000',
     * for 100.00 €) as discounts. The last option requires false as second argument. Discounts can
     * also either applied on the full amount (net-price * quantity) or on a per-unit basis.
     * Discounts are ALWAYS applied on the exclusive net-price.
     * @param int|float|string|FactorValue|MoneyValue|Price $factorOrAmount
     * @param boolean $isFactor
     * @param boolean $perUnit
     * @param mixed $value
     * @return self
     */
    public function addDiscount($value, bool $isFactor = true, bool $perUnit = false): self
    {
        if (!($value instanceof BaseValue)) {
            $value = $isFactor ? new FactorValue($value) : new MoneyValue($this->parsePrice($value));
        }
        $this->discounts[] = new DiscountValue($value, $perUnit);

        return $this;
    }

    /**
     * Set primary vat to this record.
     * The PriceBag supports one single VAT (which should fit most countries) only, however, you
     * can always add additional taxes using the `addTax` method. In fact, you can also skip `setVat`
     * completely and use `addTax` only. Each tax is ALWAYS calculated from the exclusive, discount-
     * applied net-price.
     * @param integer|float|FactorValue $value
     * @throws PriceBagException
     * @return self
     */
    public function setVat($value): self
    {
        $this->vat = $value instanceof FactorValue ? $value : new FactorValue($value);

        return $this;
    }

    /**
     * Add additional tax to record.
     * You can pass tax-factors (ex. 10.5, for 10.5% of the net-value) or a declared fixed amount
     * (ex. '10000', for 100.00 €). The last option requires false as second argument. We recommend
     * using the setVat for VAT-values but, however, you can use this method as well. All taxes are
     * ALWAYS calculated from the exclusive, discount-applied net-price.
     * @param int|float|string|FactorValue|MoneyValue|Price $value
     * @param boolean $isFactor
     * @return self
     */
    public function addTax($value, bool $isFactor = true): self
    {
        if (!($value instanceof BaseValue)) {
            $value = $isFactor ? new FactorValue($value) : new MoneyValue($this->parsePrice($value));
        }
        $this->taxes[] = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function exclusive(int $roundingMode = RoundingMode::HALF_UP): PriceValue
    {
        $price = clone $this->price;
        $price = $this->cleanExclusive($price);

        return new PriceValue($price);
    }

    /**
     * @inheritDoc
     */
    public function discount(): Money
    {
        $exclusive = $this->exclusive()->value();

        $discount = Money::ofMinor('0', $this->currency);

        foreach ($this->discounts as $item) {
            $clean = false;

            $value = $item->value();

            if ($value instanceof FactorValue) {
                $price = $value->valueOf($exclusive);
            } else {
                $clean = $this->priceInclusive == true;
                $price = $value->value();
            }

            /** @var Money $price */
            if ($item->isPerUnit()) {
                $price->multipliedBy($this->price->units());
            }

            if ($clean) {
                $price = $this->cleanDiscount($price);
            }
            $discount = $discount->plus($price);
        }
        
        return $discount;
    }

    /**
     * @inheritDoc
     */
    public function vat(): Money
    {
        if (empty($this->vat)) {
            return Money::of(0, $this->currency);
        }

        $exclusive = $this->exclusive()->exclusive();
        $discount = $this->discount();

        $price = new Price($exclusive);
        $price->minus($discount);

        return $this->vat->valueOf($price->base());
    }

    /**
     * @inheritDoc
     */
    public function factor()
    {
        if (empty($this->vat)) {
            return 0;
        } else {
            return $this->vat->value();
        }
    }

    /**
     * @inheritDoc
     */
    public function tax(bool $excludeVat = false): Money
    {
        $exclusive = $this->exclusive()->exclusive();
        $discount = $this->discount();
        
        $price = Price::parse('0', $this->currency);
        $original = new Price($exclusive);
        $original->minus($discount);

        // Add VAT
        if ($this->vat && $excludeVat != true) {
            $price->plus($this->vat->valueOf($original));
        }

        // Add Taxes
        foreach ($this->taxes as $tax) {
            if ($tax instanceof FactorValue) {
                $value = $tax->valueOf($original);
            } else {
                $value = $tax->value();
            }
            $price->plus($value);
        }

        return $price->base();
    }

    /**
     * Return detailed version of all taxes, based on the original net-price minus discount.
     * @param $detailed
     * @return array
     */
    public function taxes(bool $detailed = false): array
    {
        $original = new Price($this->exclusive()->exclusive());
        $original->minus($this->discount());
        
        $taxes = [];
        
        // VAT
        if ($this->vat) {
            $value = $this->vat->valueOf(clone $original);

            if ($detailed) {
                $taxes['vat'] = [
                    'base'      => clone $original,
                    'factor'    => $this->vat->value(),
                    'value'     => clone $value,
                ];
            } else {
                $taxes['vat'] = $value;
            }
        }

        // Additional Taxes
        $taxes['taxes'] = [];

        foreach ($this->taxes as $tax) {
            if ($tax instanceof FactorValue) {
                $value = $tax->valueOf(clone $original);
            } else {
                $value = $tax->value();
            }

            if ($tax instanceof FactorValue) {
                if ($detailed) {
                    $taxes['taxes'][] = [
                        'base'      => clone $original,
                        'factor'    => $tax->value(),
                        'value'     => clone $value,
                    ];
                } else {
                    $taxes['taxes'][] = $value;
                }
            } else {
                if ($detailed) {
                    $taxes['taxes'][] = [
                        'amount'    => $value,
                    ];
                } else {
                    $taxes['taxes'][] = $value;
                }
            }
        }

        return $taxes;
    }

    /**
     * @inheritDoc
     */
    public function inclusive(): PriceValue
    {
        $price = new Price($this->exclusive()->exclusive());
        $price->minus($this->discount());
        $price->plus($this->tax());
        
        $price = $this->cleanInclusive($price);

        return new PriceValue($price);
    }

    /**
     * Return record type.
     * @return string
     */
    abstract protected function type(): string;
}
