<?php namespace OFFLINE\Mall\Models;

use Illuminate\Support\Facades\Queue;
use Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Jobs\BrandChangeUpdate;
use System\Models\File;

class Brand extends Model
{
    use Validation;
    use Sortable;
    use Sluggable;

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = [
        'name',
        'description',
        'website',
    ];
    public $slugs = [
        'slug' => 'name',
    ];
    public $rules = [
        'name'    => 'required',
        'website' => 'url',
    ];
    public $fillable = [
        'name',
        'slug',
        'description',
        'website',
        'sort_order',
    ];
    public $table = 'offline_mall_brands';
    public $attachOne = [
        'logo' => File::class,
    ];
    public $hasMany = [
        'products' => Product::class,
    ];

    public function afterDelete()
    {
        Product::orderBy('id')
               ->where('brand_id', $this->id)
               ->chunk(100, function ($products) {
                   $data = [
                       'ids' => $products->pluck('id'),
                   ];
                   Queue::push(BrandChangeUpdate::class, $data);
               });
    }

    public function toArray()
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
