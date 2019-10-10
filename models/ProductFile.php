<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Validation;
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
        'version'      => 'required',
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
}
