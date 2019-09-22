<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Validation;

class ReviewCategory extends Model
{
    use Validation;
    use Sluggable;

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = [
        'name',
    ];
    public $fillable = [
        'name',
    ];
    public $table = 'offline_mall_review_categories';
    public $slugs = [
        'slug' => 'name',
    ];
    public $rules = [
        'name' => 'required',
    ];
    public $belongsToMany = [
        'categories' => [
            Category::class,
            'table' => 'offline_mall_category_review_category',
        ]
    ];
}
