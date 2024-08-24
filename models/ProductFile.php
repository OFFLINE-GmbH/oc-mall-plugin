<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use October\Rain\Element\ElementHolder;
use System\Models\File;

class ProductFile extends Model
{
    use Validation;

    public $table = 'offline_mall_product_files';

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

    public $translatable = [
        'display_name',
    ];

    public $rules = [
        'display_name' => 'required',
        'file'         => 'required',
    ];

    public $casts = [
        'download_count' => 'integer',
    ];

    public $attachOne = [
        'file' => [File::class, 'public' => false],
    ];

    public $belongsTo = [
        'product' => Product::class,
    ];

    public $belongsToMany = [
        'variants' => [
            Variant::class,
            'table' => 'offline_mall_product_file_variant',
        ],
    ];

    /**
     * Get the current file version first.
     * This scope is used in the backend relation list
     * on the products form.
     *
     * @param $q
     */
    public function scopeSortLatest($q)
    {
        $q->orderBy('created_at', 'DESC');
    }

    /**
     * Filter shown fields on backend form.
     * @param ElementHolder $fields
     * @param mixed $context
     * @return void
     */
    public function filterFields(ElementHolder $fields, $context = null)
    {
        if ($this->product->inventory_management_method == 'single') {
            if (is_a($fields, ElementHolder::class) && array_key_exists('variants', $fields->config)) {
                $fields->config['variants']->hidden = true;
            } elseif (property_exists($fields, 'variants')) {
                $fields->variants->hidden = true;
            }
        }
    }
}
