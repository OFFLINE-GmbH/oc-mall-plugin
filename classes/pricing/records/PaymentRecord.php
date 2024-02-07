<?php declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Records;

use Brick\Money\Money;
use OFFLINE\Mall\Classes\Exceptions\PriceBagException;
use OFFLINE\Mall\Classes\Pricing\Values\FactorValue;
use Whitecube\Price\Price;

class PaymentRecord extends AbstractItemRecord
{
    const TYPE = 'payment';

    /**
     * Return record type
     * @return string
     */
    protected function type(): string
    {
        return self::TYPE;
    }

    /**
     * Create a new payment record.
     * @param string $currency
     * @param integer|float|string|Price $amount
     * @param boolean $isFactor
     */
    public function __construct(string $currency, int|float|string|Price $amount, bool $isFactor = false)
    {
        if ($amount instanceof Price) {
            $amount->setUnits(1);
        } else if (!($amount instanceof Price)) {
            if (is_string($amount)) {
                $amount = Price::parse($amount, $currency, 1);
            } else {
                $amount = new Price(Money::ofMinor($amount, $currency), 1);
            }
        }

        $this->currency = $currency;
        $this->price = $amount;
        $this->priceInclusive = false;
    }

    /**
     * @inheritDoc
     */
    public function setVat(int|float|FactorValue $value): self
    {
        throw new PriceBagException('The PaymentRecord class does not support VAT values, use taxes only.');
    }

    /**
     * @inheritDoc
     */
    public function factor(): int|float
    {
        throw new PriceBagException('The PaymentRecord class does not support VAT values, use taxes only.');
    }

    /**
     * @inheritDoc
     */
    public function vat(): Money
    {
        throw new PriceBagException('The PaymentRecord class does not support VAT values, use taxes only.');
    }
}
