<?php

namespace OFFLINE\Mall\Classes\Observers;

use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Models\PropertyValue;

class PropertyValueObserver
{
    protected $index;

    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    public function created(PropertyValue $value)
    {
        $this->handle($value);
    }

    public function updated(PropertyValue $value)
    {
        $this->handle($value);
    }

    public function deleted(PropertyValue $value)
    {
        $this->handle($value);
    }

    protected function handle(PropertyValue $value)
    {
        if ($value->product) {
            (new ProductObserver($this->index))->updated(($value->product));
        }
        if ($value->variant) {
            (new VariantObserver($this->index))->updated(($value->variant));
        }
    }
}
