<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use System\Models\File;

class Brand extends Model
{
    use Validation;
    use Sortable;

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = [
        'name',
        'website',
    ];

    public $rules = [
        'name'    => 'required',
        'website' => 'url',
    ];

    public $table = 'offline_mall_brands';

    public $attachOne = [
        'logo' => File::class,
    ];

    public $hasMany = [
        'products' => Product::class,
    ];
}
