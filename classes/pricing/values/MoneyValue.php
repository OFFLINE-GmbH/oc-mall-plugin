<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Values;

use Brick\Money\AbstractMoney;
use Brick\Money\Money;
use OFFLINE\Mall\Classes\Exceptions\PriceBagException;
use OFFLINE\Mall\Classes\Pricing\BaseValue;
use Whitecube\Price\Price;

class MoneyValue extends BaseValue
{
    /**
     * The desired money instance.
     * @var Money
     */
    protected Money $money;

    /**
     * Create a new MoneyValue.
     * @param AbstractMoney|Money|Price $money
     */
    public function __construct($money)
    {
        if ($money instanceof Price) {
            $money = $money->base(false);
        }

        if (!($money instanceof Money)) {
            throw new PriceBagException('The MoneyValue class currently only supports Brick\\Money\\Money objects.');
        }
        $this->money = $money;
    }

    /**
     * Cloned class object should have his own cloned money instance.
     * @return void
     */
    public function __clone()
    {
        $this->money = clone $this->money;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return strval($this->money);
    }

    /**
     * Return copy of the internal money object.
     * @return Money
     */
    public function value(): Money
    {
        return clone $this->money;
    }

    /**
     * Return copy of the internal money object as Price object.
     * @return Price
     */
    public function price(): Price
    {
        return new Price(clone $this->money);
    }

    /**
     * @inheritDoc
     */
    public function toInt(): int
    {
        return $this->money->getMinorAmount()->toInt();
    }

    /**
     * @inheritDoc
     */
    public function toFloat(): float
    {
        return $this->money->getAmount()->toFloat();
    }
}
