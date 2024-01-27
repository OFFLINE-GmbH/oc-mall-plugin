<?php

namespace OFFLINE\Mall\Classes\Traits;

use Hashids\Hashids as Hasher;

trait HashIds
{
    /**
     * To hide the original ID in the product URL we use hash
     * ids to link to different variants.
     *
     * @return string
     */
    public function getHashIdAttribute()
    {
        return app(Hasher::class)->encode($this->attributes['id']);
    }

    /**
     * Decode string value.
     * @param string $value
     * @return mixed
     */
    public function decode(string $value)
    {
        return app(Hasher::class)->decode($value) ?? null;
    }

    /**
     * Encode numeric value(s).
     * @param int|int[] $value
     * @return mixed
     */
    public function encode(mixed $value)
    {
        return app(Hasher::class)->encode($value);
    }
}
