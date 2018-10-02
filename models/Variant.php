<?php namespace OFFLINE\Mall\Models;

use Illuminate\Support\Collection;
use Model;
use October\Rain\Database\Traits\Nullable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\CustomFields;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\Images;
use OFFLINE\Mall\Classes\Traits\PriceAccessors;
use OFFLINE\Mall\Classes\Traits\ProductPriceAccessors;
use OFFLINE\Mall\Classes\Traits\StockAndQuantity;
use OFFLINE\Mall\Classes\Traits\UserSpecificPrice;
use System\Models\File;

class Variant extends Model
{
    use Validation;
    use SoftDelete;
    use Images;
    use HashIds;
    use CustomFields;
    use UserSpecificPrice;
    use Nullable;
    use PriceAccessors;
    use ProductPriceAccessors;
    use StockAndQuantity;

    const MORPH_KEY = 'mall.variant';

    public $slugs = [];
    public $nullable = ['image_set_id'];
    public $table = 'offline_mall_product_variants';
    public $dates = ['deleted_at'];
    public $with = ['product.additional_prices', 'image_sets', 'prices', 'additional_prices'];
    public $casts = [
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
        'id'                           => 'integer',
        'stock'                        => 'integer',
        'sales_count'                  => 'integer',
        'weight'                       => 'integer',
    ];
    public $rules = [
        'name'                         => 'required',
        'product_id'                   => 'required|exists:offline_mall_products,id',
        'stock'                        => 'required|integer',
        'weight'                       => 'nullable|integer',
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
    ];
    public $attachMany = [
        'temp_images' => File::class,
        'downloads'   => File::class,
    ];
    public $belongsTo = [
        'product'      => Product::class,
        'cart_product' => CartProduct::class,
        'image_sets'   => [ImageSet::class, 'key' => 'image_set_id'],
    ];
    public $hasMany = [
        'prices'              => ProductPrice::class,
        'property_values'     => [PropertyValue::class, 'key' => 'variant_id', 'otherKey' => 'id'],
        'all_property_values' => [
            PropertyValue::class,
            'key'      => 'variant_id',
            'otherKey' => 'id',
            'scope'    => 'withInherited',
        ],
    ];
    public $morphMany = [
        'customer_group_prices' => [CustomerGroupPrice::class, 'name' => 'priceable'],
        'additional_prices'     => [Price::class, 'name' => 'priceable'],
    ];
    protected $fillable = [
        'product_id',
        'user_defined_id',
        'image_set_id',
        'stock',
        'name',
        'published',
        'weight',
        'allow_out_of_stock_purchases',
    ];

    public static function boot()
    {
        parent::boot();
        static::saved(function (Variant $variant) {
            if ($variant->image_set_id === null) {
                $variant->createImageSetFromTempImages();
            }

            $values = post('VariantPropertyValues');
            if ( ! $values) {
                return;
            }

            foreach ($values as $id => $value) {
                $pv = PropertyValue::firstOrNew([
                    'variant_id'  => $variant->id,
                    'product_id'  => $variant->product_id,
                    'property_id' => $id,
                ]);

                $pv->value = $value;
                $pv->save();
            }
        });
    }

    protected function createImageSetFromTempImages()
    {
        $tempImages = $this->temp_images()
                           ->withDeferred(post('_session_key'))
                           ->count();

        if ($tempImages < 1) {
            return;
        }

        return \DB::transaction(function () {
            $set             = new ImageSet();
            $set->name       = $this->name;
            $set->product_id = $this->product_id;
            $set->save();

            $this->image_set_id = $set->id;
            $this->save();

            $this->commitDeferred(post('_session_key'));

            return \DB::table('system_files')
                      ->where('attachment_type', Variant::MORPH_KEY)
                      ->where('attachment_id', $this->id)
                      ->where('field', 'temp_images')
                      ->update([
                          'attachment_type' => ImageSet::MORPH_KEY,
                          'attachment_id'   => $set->id,
                          'field'           => 'images',
                      ]);
        });
    }

    public function getImageSetIdOptions()
    {
        $null = [
            '' => '-- ' . trans('offline.mall::lang.image_sets.create_new'),
        ];

        $sets = Product::find(post('id', $this->product_id))->image_sets;
        if ( ! $sets) {
            return $null;
        }

        return $null + $sets->pluck('name', 'id')->toArray();
    }

    public function custom_fields()
    {
        return $this->product->custom_fields();
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function getAttribute($attribute)
    {
        $originalValue       = parent::getAttribute($attribute);
        $inheritanceDisabled = session()->get('mall.variants.disable-inheritance');

        // If any of the product relation columns are called don't override the method's default behaviour.
        if ($inheritanceDisabled || \in_array($attribute, ['product', 'product_id'])) {
            return $originalValue;
        }

        $parentValues = $this->product->getAttribute($attribute);

        // In case of an empty Array or Collection we want to
        // return the parent's values
        $empty = $this->isEmptyCollection($originalValue);

        if ($attribute !== 'prices' || $empty) {
            return $originalValue === null || $empty ? $parentValues : $originalValue;
        }

        // Inherit parent pricing info.
        return $originalValue->map(function ($price) use ($parentValues) {
            return $price->price !== null
                ? $price
                : $parentValues->where('currency_id', $price->currency_id)->first();
        });
    }

    /**
     * This setter makes it easier to set price values
     * in different currencies by providing an array of
     * prices. It is mostly used for unit testing.
     *
     * @internal
     *
     * @param $value
     */
    public function setPriceAttribute($value)
    {
        if ( ! is_array($value)) {
            return;
        }
        foreach ($value as $currency => $price) {
            ProductPrice::updateOrCreate([
                'variant_id'  => $this->id,
                'product_id'  => $this->product->id,
                'currency_id' => Currency::where('code', $currency)->firstOrFail()->id,
            ], [
                'price' => $price === null ? null : ($price / 100),
            ]);
        }
    }

    /**
     * To easily generate the correct URL to a Product/Variant
     * we blindly call item.variantId. In this case we return
     * the Variant's hashed ID. If the property is called on a
     * Product model null is returned.
     * @return string
     */
    public function getVariantIdAttribute()
    {
        return $this->hashId;
    }

    public function getPropertiesDescriptionAttribute()
    {
        return $this->propertyValuesAsString();
    }

    public function propertyValuesAsString()
    {
        return $this->property_values
            ->reject(function (PropertyValue $value) {
                return $value->value === '' || $value->value === null || $value->property === null;
            })
            ->map(function (PropertyValue $value) {
                // display_value is already escaped in PropertyValue::getDisplayValueAttribute()
                return sprintf('%s: %s', e($value->property->name), $value->display_value);
            })->implode('<br />');
    }

    protected function isEmptyCollection($originalValue): bool
    {
        if ($originalValue instanceof Collection) {
            return $originalValue->count() < 1;
        }

        if (is_array($originalValue)) {
            return count($originalValue) < 1;
        }

        return false;
    }
}
