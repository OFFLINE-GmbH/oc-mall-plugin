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
        $result = app(Hasher::class)->decode($value) ?? null;
        if (is_array($result) && count($result) === 1) {
            return $result[0];
        } else {
            return $result;
        }
    }

    /**
     * Encode numeric value(s).
     * @param int|int[] $value
     * @return mixed
     */
    public function encode($value)
    {
        return app(Hasher::class)->encode($value);
    }
}
