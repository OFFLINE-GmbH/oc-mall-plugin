<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Records;

use OFFLINE\Mall\Classes\Exceptions\PriceBagException;
use OFFLINE\Mall\Classes\Pricing\BaseRecord;
use OFFLINE\Mall\Classes\Pricing\BaseValue;
use OFFLINE\Mall\Classes\Pricing\Values\FactorValue;
use OFFLINE\Mall\Classes\Pricing\Values\MoneyValue;
use OFFLINE\Mall\Classes\Pricing\Values\PriceValue;
use OFFLINE\Mall\Models\Discount;
use Whitecube\Price\Price;

class DiscountRecord extends BaseRecord
{
    public const TYPE = 'discount';

    /**
     * Amount
     * @var FactorValue|MoneyValue
     */
    protected $amount;

    /**
     * Create a new shipping record.
     * @param string $currency
     * @param int|float|string|FactorValue|MoneyValue|Price $amount
     * @param bool $isFactor
     */
    public function __construct(string $currency, $amount, bool $isFactor = false)
    {
        $this->currency = $currency;

        if (!($amount instanceof BaseValue)) {
            $amount = $isFactor ? new FactorValue($amount) : new MoneyValue($this->parsePrice($amount));
        }

        $this->amount = $amount;
        $this->price = Price::parse('0', $this->currency);
        $this->priceInclusive = false;
    }

    /**
     * Convert record object to array.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'  => self::TYPE,
        ];
    }

    /**
     * Return associates type.
     * @return null|string
     */
    public function type()
    {
        if (empty($this->model)) {
            return null;
        } elseif (is_string($this->model)) {
            return $this->model;
        } else {
            /** @var Discount $model */
            $model = $this->model;

            return ($model->type == 'shipping') ? 'shipping' : 'products';
        }
    }

    /**
     * Receive discount amount.
     * @return FactorValue|MoneyValue
     */
    public function amount()
    {
        return $this->amount;
    }

    /**
     * @inheritDoc
     */
    public function exclusive(): PriceValue
    {
        if ($this->amount instanceof FactorValue) {
            $value = $this->amount->valueOf($this->price);
        } else {
            $value = $this->amount->value();
        }

        return new PriceValue($value);
    }

    /**
     * @inheritDoc
     */
    public function discount()
    {
        throw new PriceBagException('The DiscountRecord class does not support the discounts method.');
    }

    /**
     * @inheritDoc
     */
    public function vat()
    {
        throw new PriceBagException('The DiscountRecord class does not support the vat method.');
    }

    /**
     * @inheritDoc
     */
    public function factor()
    {
        throw new PriceBagException('The DiscountRecord class does not support the factor method.');
    }

    /**
     * @inheritDoc
     */
    public function tax(bool $excludeVat = false)
    {
        throw new PriceBagException('The DiscountRecord class does not support the taxes method.');
    }

    /**
     * @inheritDoc
     */
    public function inclusive(): PriceValue
    {
        return $this->exclusive();
    }
}
