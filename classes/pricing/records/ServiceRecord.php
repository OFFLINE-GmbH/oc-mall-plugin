<?php declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Pricing\Records;

class ServiceRecord extends AbstractItemRecord
{
    const TYPE = 'service';

    /**
     * Return record type
     * @return string
     */
    protected function type(): string
    {
        return self::TYPE;
    }
}
