<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Records;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Model;
use OFFLINE\Mall\Classes\Exceptions\PriceBagException;
use OFFLINE\Mall\Classes\Pricing\Values\FactorValue;
use OFFLINE\Mall\Classes\Pricing\Values\MoneyValue;
use OFFLINE\Mall\Classes\Pricing\Values\PriceValue;
use OFFLINE\Mall\Models\Discount;
use Whitecube\Price\Price;

class ShippingRecord extends AbstractItemRecord
{
    public const TYPE = 'shipping';

    /**
     * The original associated Price model of this record.
     * @var ?Price
     */
    protected ?Price $original = null;

    /**
     * The associated Discount model, when set.
     * @var array
     */
    protected ?Model $discountModel = null;

    /**
     * Required packages for this delivery.
     * @var integer
     */
    protected int $packages = 1;

    /**
     * Additional shipping costs rates.
     * @var array
     */
    protected array $rates = [];

    /**
     * Create a new shipping record.
     * @param string $currency
     * @param integer|float|string|Price $amount
     * @param boolean $isInclusive
     */
    public function __construct(string $currency, $amount, bool $isInclusive = false)
    {
        if ($amount instanceof Price) {
            $packages = ceil($amount->units());
            $amount->setUnits(1);
            $this->packages = $packages;
        } elseif (!($amount instanceof Price)) {
            if (is_string($amount)) {
                $amount = Price::parse($amount, $currency, 1);
            } else {
                $amount = new Price(Money::ofMinor($amount, $currency), 1);
            }
        }

        $this->currency = $currency;
        $this->price = $amount;
        $this->priceInclusive = $isInclusive;
    }

    /**
     * Add discount to this record.
     * @param int|float|string|FactorValue|MoneyValue|Price $factorOrAmount
     * @param boolean $isFactor
     * @param boolean $perUnit
     * @param mixed $value
     * @return self
     */
    public function addDiscount($value, bool $isFactor = true, bool $perUnit = false): self
    {
        throw new PriceBagException('The ShippingRecord class does not support multiple discounts, use setAmount instead.');
    }

    /**
     * Overwrite existing amount of this record.
     * @param int|float|string|Price $amount
     * @param null|Discount $discount Associated discount model.
     * @return self
     */
    public function setAmount($amount, ?Discount $discount = null): self
    {
        if ($amount instanceof Price) {
            $amount->setUnits(1);
        } elseif (!($amount instanceof Price)) {
            if (is_string($amount)) {
                $amount = Price::parse($amount, $this->currency, 1);
            } else {
                $amount = new Price(Money::ofMinor($amount, $this->currency), 1);
            }
        }

        $this->original = $this->price;
        $this->price = $amount;
        $this->discountModel = $discount;

        return $this;
    }

    /**
     * Reset Overwritten amount.
     * @return self
     */
    public function resetAmount(): self
    {
        $this->price = $this->original;
        $this->original = null;
        $this->discountModel = null;

        return $this;
    }

    /**
     * Add additional shipping costs rate.
     * @param integer|null $from
     * @param integer|null $until
     * @param integer|float|string|Price $amount
     * @return self
     */
    public function addRate(?int $from, ?int $until, $amount): self
    {
        if (!($amount instanceof Price)) {
            if (is_string($amount)) {
                $amount = Price::parse($amount, $this->currency, 1);
            } else {
                $amount = new Price(Money::ofMinor($amount, $this->currency), 1);
            }
        }

        $this->rates[] = [$from ?? 0, $until ?? 0, $amount];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function exclusive(int $roundingMode = RoundingMode::HALF_UP): PriceValue
    {
        if (empty($this->rates) || !$this->bag) {
            $exclusive = parent::exclusive($roundingMode);

            return new PriceValue($exclusive->value()->multipliedBy($this->packages));
        } else {
            $original = clone $this->price;

            // Find amount by weight rate
            $amount = null;
            $weight = $this->bag->productsWeight();

            foreach ($this->rates as $rate) {
                if ($weight >= $rate[0]) {
                    if ($rate[1] === 0 || $weight <= $rate[1]) {
                        $amount = $rate[2];
                        break;
                    }
                }
            }

            // Return default
            if (empty($amount)) {
                $exclusive = parent::exclusive($roundingMode);

                return new PriceValue($exclusive->value()->multipliedBy($this->packages));
            }

            // Temporary Overwrite amount
            $this->price = $amount;
            $exclusive = parent::exclusive($roundingMode);
            $result = new PriceValue($exclusive->value()->multipliedBy($this->packages));
            $this->price = $original;

            return $result;
        }
    }

    /**
     * @inheritDoc
     */
    public function vat(): Money
    {
        /** @var Money */
        $vat = parent::vat();
        $vat->multipliedBy($this->packages);

        /** @var Money */
        return $vat;
    }

    /**
     * @inheritDoc
     */
    public function tax(bool $excludeVat = false): Money
    {
        /** @var Money */
        $taxes = parent::tax($excludeVat);
        $taxes->multipliedBy($this->packages);

        /** @var Money */
        return $taxes;
    }

    /**
     * @inheritDoc
     */
    public function inclusive(): PriceValue
    {
        $inclusive = parent::inclusive();

        return new PriceValue($inclusive->value()->multipliedBy($this->packages));
    }

    /**
     * Return record type
     * @return string
     */
    protected function type(): string
    {
        return self::TYPE;
    }
}
