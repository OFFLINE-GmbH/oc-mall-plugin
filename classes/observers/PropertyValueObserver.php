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
        // Skip the re-index for the backend relation updates. The re-index will
        // be triggered manually for performance reasons.
        if ($this->isBackendRelationUpdate()) {
            return;
        }
        if ($value->product && $value->product->skipReindex !== true) {
            (new ProductObserver($this->index))->updated($value->product);
        }
        if ($value->variant && $value->variant->skipReindex !== true) {
            (new VariantObserver($this->index))->updated($value->variant);
        }
    }

    /**
     * @return bool
     */
    protected function isBackendRelationUpdate(): bool
    {
        return app()->runningInBackend()
            && post('_relation_field') === 'variants'
            && post('_relation_mode') === 'form';
    }
}
