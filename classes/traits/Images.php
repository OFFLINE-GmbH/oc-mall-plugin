<?php


namespace OFFLINE\Mall\Classes\Traits;

use October\Rain\Support\Collection;
use System\Models\File;

trait Images
{
    /**
     * Return the main image, if one is uploaded. Otherwise
     * use the first available image.
     *
     * @return File
     */
    public function getImageAttribute()
    {
        if ($this->main_image) {
            return $this->main_image;
        }

        if ($this->images) {
            return $this->images->first();
        }
    }

    /**
     * Return all available images.
     *
     * @return File
     */
    public function getAllImagesAttribute()
    {
        if ( ! $this->main_image) {
            return $this->images;
        }

        // To prevent the mutation of the original images relationship
        // property we create a new collection and return it instead.
        return collect([$this->main_image])->concat($this->images->unique());
    }

    /**
     * Return all images except the main image.
     *
     * @return Collection
     */
    public function getAdditionalImagesAttribute()
    {
        // If a main image exists for this product we
        // can just return all additional images.
        if ($this->main_image) {
            return $this->images;
        }

        // If no main image is uploaded we have to exclude the
        // alternatively selected main image form the collection.
        $mainImage = $this->image;

        return $this->images->reject(function ($item) use ($mainImage) {
            return $item->id === $mainImage->id;
        });
    }
}
