<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sortable;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\Price;
use System\Models\File;

/**
 * Model
 */
class CustomFieldOption extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use Price;
    use Sortable;
    use HashIds;

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = ['name'];
    public $jsonable = ['values'];
    public $fillable = [
        'id',
        'name',
        'price',
        'values',
        'sort_order',
        'custom_field_id',
    ];

    public $rules = [
        'name'  => 'required',
        'price' => 'nullable|regex:/\d+([\.,]\d+)?/i',
    ];

    public $attachOne = [
        'image' => File::class,
    ];

    public $belongsTo = [
        'product'      => Product::class,
        'custom_field' => CustomField::class,
    ];

    /**
     * The parent's field type is store to make trigger conditions
     * work in the custom backend relationship form.
     *
     * @var string
     */
    public $field_type = '';

    public $table = 'offline_mall_custom_field_options';

    /**
     * Make it easiert to access the first "values" option since
     * this often is the only value we need.
     */
    public function getValueAttribute()
    {
        return $this->values[0] ?? null;
    }
}
