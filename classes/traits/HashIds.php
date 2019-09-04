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
     * @return mixed
     */
    public function decode($value)
    {
        $value = app(Hasher::class)->decode($value);

        return $value[0] ?? null;
    }

    /**
     * @return mixed
     */
    public function encode($value)
    {
        return app(Hasher::class)->encode($value);
    }
}
