<?php namespace OFFLINE\Mall\Models;

use DB;
use Model;
use System\Models\File;

class ImageSet extends Model
{
    use \October\Rain\Database\Traits\Validation;

    const MORPH_KEY = 'mall.imageset';

    public $with = ['images'];
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = [
        'name'
    ];

    protected $fillable = ['name', 'is_main_set', 'product_id'];
    public $table = 'offline_mall_image_sets';
    public $rules = [
        'name' => 'required',
    ];
    public $attachMany = [
        'images' => File::class,
    ];
    public $belongsTo = [
        'product' => Product::class,
    ];

    public static function boot()
    {
        parent::boot();
        static::saving(function (self $set) {
            $set->handleMainSetStatus();
        });
        static::deleted(function (self $set) {
            DB::table('offline_mall_product_variants')
              ->where('image_set_id', $set->id)
              ->update(['image_set_id' => null]);
        });
    }

    /**
     * Makes sure that there is only one main set
     * for a given product.
     */
    protected function handleMainSetStatus()
    {
        $existingSets = DB::table('offline_mall_image_sets')
                          ->where('product_id', $this->product_id)
                          ->count();

        if ($existingSets === 0) {
            return $this->is_main_set = true;
        }

        if ($this->is_main_set) {
            DB::table('offline_mall_image_sets')
              ->where('product_id', $this->product_id)
              ->where('id', '<>', $this->id)
              ->update(['is_main_set' => false]);
        }
    }
}
