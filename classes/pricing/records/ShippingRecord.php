<?php declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Records;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use OFFLINE\Mall\Classes\Pricing\Values\PriceValue;
use Whitecube\Price\Price;

class ShippingRecord extends AbstractItemRecord
{
    const TYPE = 'shipping';

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
     * Return record type
     * @return string
     */
    protected function type(): string
    {
        return self::TYPE;
    }

    /**
     * Create a new shipping record.
     * @param string $currency
     * @param integer|float|string|Price $amount
     * @param boolean $isInclusive
     */
    public function __construct(string $currency, int|float|string|Price $amount, bool $isInclusive = false)
    {
        if ($amount instanceof Price) {
            $packages = ceil($amount->units());
            $amount->setUnits(1);
            $this->packages = $packages;
        } else if (!($amount instanceof Price)) {
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
     * Add additional shipping costs rate.
     * @param integer|null $from
     * @param integer|null $until
     * @param integer|float|string|Price $amount
     * @return self
     */
    public function addRate(?int $from, ?int $until, int|float|string|Price $amount): self
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
            foreach ($this->rates AS $rate) {
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
}
