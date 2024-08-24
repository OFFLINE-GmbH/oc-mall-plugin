<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use DB;
use Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Database\IsStates;

class PriceCategory extends Model
{
    use IsStates;
    use Sluggable;
    use Sortable;
    use Validation;

    /**
     * Former identification of the 'old_price' model item.
     * @deprecated
     */
    public const OLD_PRICE_CATEGORY_ID = 1;

    /**
     * Disable `is_default` handler on IsStates trait.
     * @var null|string
     */
    public const IS_DEFAULT = null;

    /**
     * Enable `is_enabled` handler on IsStates trait, by passing the column name.
     * @var null|string
     */
    public const IS_ENABLED = 'is_enabled';

    /**
     * Implement behaviors for this model.
     * @var array
     */
    public $implement = [
        '@RainLab.Translate.Behaviors.TranslatableModel',
    ];
    
    /**
     * The table associated with this model.
     * @var string
     */
    public $table = 'offline_mall_price_categories';

    /**
     * The translatable attributes of this model.
     * @var array
     */
    public $translatable = [
        'name',
        'title',
    ];

    /**
     * The validation rules for the single attributes.
     * @var array
     */
    public $rules = [
        'name'          => 'required',
        'code'          => 'required',
        'title'         => 'nullable',
        'is_enabled'    => 'nullable|boolean',
    ];

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
    public $fillable = [
        'name',
        'code',
        'title',
        'is_enabled',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    public $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * Automatically generate unique URL names for the passed attributes.
     * @var array
     */
    public $slugs = [
        'code' => 'name',
    ];

    /**
     * The hasMany relationships of this model.
     * @var array
     */
    public $hasMany = [
        'prices' => [
            Price::class,
            'key' => 'price_category_id',
        ],
    ];

    /**
     * Hook after model has been deleted.
     * @return void
     */
    public function afterDelete()
    {
        DB::table('offline_mall_prices')->where('price_category_id', $this->id)->delete();
    }
}
