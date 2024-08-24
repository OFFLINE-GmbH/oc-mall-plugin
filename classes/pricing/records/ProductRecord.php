<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Records;

class ProductRecord extends AbstractItemRecord
{
    public const TYPE = 'product';

    /**
     * The assigned weight per unit.
     * @var null|integer|float
     */
    protected $weight = null;

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['exclusiveUnit'] = strval($this->cleanExclusive((clone $this->price)->setUnits(1)));
        $array['units'] = $this->price->units();

        return $array;
    }

    /**
     * Set weight of the assigned product / unit.
     * @param integer|float $weight
     * @return self
     */
    public function setWeight($weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Return calculated weight for all units of this record.
     * @return null|int|float
     */
    public function weight()
    {
        if (empty($this->weight)) {
            return 0;
        } else {
            return $this->weight * $this->price->units();
        }
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
