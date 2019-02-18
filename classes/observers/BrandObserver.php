<?php

namespace OFFLINE\Mall\Classes\Observers;

use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Models\Brand;

class BrandObserver
{
    protected $index;

    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    public function created(Brand $brand)
    {
    }

    public function updated(Brand $brand)
    {
        $this->handle($brand);
    }

    public function deleted(Brand $brand)
    {
        $this->handle($brand);
    }

    protected function handle(Brand $brand)
    {

        if ($brand->product && $brand->product->skipReindex !== true) {
            (new ProductObserver($this->index))->updated($brand->product);
        }
        if ($brand->variant && $brand->variant->skipReindex !== true) {
            (new VariantObserver($this->index))->updated($brand->variant);
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
