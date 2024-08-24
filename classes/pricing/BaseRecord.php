<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing;

use Brick\Money\Money;
use October\Rain\Database\Model;
use OFFLINE\Mall\Classes\Exceptions\PriceBagException;
use OFFLINE\Mall\Classes\Pricing\Values\PriceValue;
use Whitecube\Price\Price;

abstract class BaseRecord
{
    /**
     * The used currency for this record.
     * @var string
     */
    protected string $currency;

    /**
     * The associated Price model of this record.
     * @var Price
     */
    protected ?Price $price;

    /**
     * Whether the passed price is inclusive vat and taxes or not.
     * @var boolean
     */
    protected bool $priceInclusive;

    /**
     * The associated PriceBag of this record.
     * @var PriceBag|null
     */
    protected ?PriceBag $bag = null;

    /**
     * The associated Model of this record.
     * @var null|string|PriceBag
     */
    protected $model = null;

    /**
     * Create a new product record.
     * @param string $currency
     * @param integer|float|string|Price $amount
     * @param integer $units
     * @param boolean $isInclusive
     */
    public function __construct(string $currency, $amount, int $units = 1, bool $isInclusive = false)
    {
        if ($amount instanceof Price && $amount->units() != $units) {
            $amount->setUnits($units);
        } elseif (!($amount instanceof Price)) {
            if (is_string($amount)) {
                $amount = Price::parse($amount, $currency, $units);
            } else {
                $amount = new Price(Money::ofMinor($amount, $currency), $units);
            }
        }

        $this->currency = $currency;
        $this->price = $amount;
        $this->priceInclusive = $isInclusive;
    }

    /**
     * Return a specific value from this record..
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->{$key} ?? null;
    }

    /**
     * Convert record object to array.
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * Return exclusive price value (price without discount, vat or taxes applied).
     * @return PriceValue
     */
    abstract public function exclusive(): PriceValue;

    /**
     * Return sum of all discounts.
     * @return null|Money
     */
    abstract public function discount();

    /**
     * Return only vat based on the original net-price minus discount.
     * @return null|Money
     */
    abstract public function vat();

    /**
     * Return raw vat factor.
     * @return int|float
     */
    abstract public function factor();

    /**
     * Return sum of all taxes (incl. or excl. vat) based on the original net-price minus discount.
     * @param bool $excludeVat Wether to include or exclude VAT.
     * @return null|Money
     */
    abstract public function tax(bool $excludeVat = false);

    /**
     * Return inclusive price value, containing discounts, vat and other taxes.
     * @return PriceValue
     */
    abstract public function inclusive(): PriceValue;

    /**
     * Set the specific units on the price for this record.
     * @param integer $units
     * @return self
     */
    public function setUnits(int $units): self
    {
        $this->price->setUnits($units);

        return $this;
    }

    /**
     * Set PriceBag and Model association for this record.
     * @param PriceBag $bag
     * @param null|string|Model $model
     * @return void
     */
    public function setAssoc(PriceBag $bag, $model = null)
    {
        if (!empty($this->bag)) {
            throw new PriceBagException('This record has already been assigned to a PriceBag instance.');
        } else {
            $this->bag = $bag;
            $this->model = $model;

            return $this;
        }
    }

    /**
     * Return the associated PriceBag instance, or null if no association has been applied.
     * @return null|PriceBag
     */
    public function bag()
    {
        return $this->bag;
    }

    /**
     * Return the associated Model instance or context string, or null if no association has been applied.
     * @return null|string|Model
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * Parse Price.
     * @param mixed $value
     * @return Price
     */
    protected function parsePrice($value): Price
    {
        if (is_string($value)) {
            return Price::parse($value, $this->currency, 1);
        } elseif (is_int($value) || is_float($value)) {
            return new Price(Money::ofMinor($value, $this->currency), 1);
        } elseif ($value instanceof Price) {
            return $value;
        } else {
            throw new PriceBagException('The passed value could not be parsed.');
        }
    }
}
