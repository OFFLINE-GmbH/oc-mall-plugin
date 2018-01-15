<?php namespace OFFLINE\Mall\Models;

use OFFLINE\Mall\Classes\Traits\CustomFields;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\Images;
use OFFLINE\Mall\Classes\Traits\Price;
use System\Models\File;

/**
 * Model
 */
class Variant extends \Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;
    use Images;
    use HashIds;
    use CustomFields;
    use Price {
        getAttribute as priceGetAttribute;
    }

    public $slugs = [];

    public $dates = ['deleted_at'];
    public $with = ['product'];

    public $casts = [
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
    ];

    public $rules = [
        'name'                         => 'required',
        'product_id'                   => 'required|exists:offline_mall_products,id',
        'stock'                        => 'integer',
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
        'price'                        => 'sometimes|nullable|regex:/\d+([\.,]\d+)?/i',
        'old_price'                    => 'sometimes|nullable|regex:/\d+([\.,]\d+)?/i',
    ];

    public $table = 'offline_mall_product_variants';

    public $attachOne = [
        'main_image' => File::class,
    ];

    public $attachMany = [
        'images'    => File::class,
        'downloads' => File::class,
    ];

    public $belongsTo = [
        'product'      => Product::class,
        'cart_product' => CartProduct::class,
    ];

    public $morphMany = [
        'property_values' => [PropertyValue::class, 'name' => 'describable'],
    ];

    /**
     * The related products data is cached to speed uf the
     * getAttribute method below.
     *
     * @var Product
     */
    protected $parent;

    public static function boot()
    {
        static::saved(function (Variant $variant) {
            $values = post('PropertyValues');
            if ( ! $values) {
                return;
            }

            foreach ($values as $id => $value) {
                $pv = PropertyValue::firstOrNew([
                    'describable_id'   => $variant->id,
                    'describable_type' => Variant::class,
                    'property_id'      => $id,
                ]);

                $pv->value = $value;
                $pv->save();
            }
        });
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function getAttribute($attribute)
    {
        if (session()->get('mall.variants.disable-inheritance')) {
            return parent::getAttribute($attribute);
        }

        $value = $this->priceGetAttribute($attribute);

        if ($value !== null || ! isset($this->attributes['product_id'])) {
            return $value;
        }

        // Cache the "parent" products data.
        if ( ! $this->parent) {
            $this->parent = Product::find($this->attributes['product_id']);
        }

        return $this->parent->getAttribute($attribute);
    }

    /**
     * Return the main image, if one is uploaded. Otherwise
     * use the first available image.
     *
     * @return File
     */
    public function getImageAttribute()
    {
        if ($this->main_image) {
            return $this->main_image;
        }

        if ($this->images) {
            return $this->images->first();
        }
    }

    public function getPriceColumns()
    {
        return ['price', 'old_price'];
    }

    public function getDescriptionAttribute()
    {
        if ($this->attributes['name']) {
            return $this->attributes['name'];
        }

        return $this->property_values
            ->reject(function (PropertyValue $value) {
                return $value->value === '' || $value->value === null;
            })
            ->map(function (PropertyValue $value) {
                return sprintf('%s: %s', e($value->property->name), $value->display_value);
            })->implode(', ');
    }
}
