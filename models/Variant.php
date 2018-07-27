<?php namespace OFFLINE\Mall\Models;

use Illuminate\Support\Collection;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Classes\Traits\CustomFields;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\Images;
use OFFLINE\Mall\Classes\Traits\Price;
use OFFLINE\Mall\Classes\Traits\UserSpecificPrice;
use System\Models\File;

class Variant extends \Model
{
    use Validation;
    use SoftDelete;
    use Images;
    use HashIds;
    use CustomFields;
    use UserSpecificPrice;
    use Price {
        getAttribute as priceGetAttribute;
    }

    const MORPH_KEY = 'mall.variant';

    public $slugs = [];
    public $dates = ['deleted_at'];
    public $with = ['product', 'image_sets'];
    public $jsonable = ['price', 'old_price'];
    public $casts = [
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
        'id'                           => 'integer',
        'stock'                        => 'integer',
        'weight'                       => 'integer',
    ];
    public $rules = [
        'name'                         => 'required',
        'product_id'                   => 'required|exists:offline_mall_products,id',
        'stock'                        => 'integer|nullable',
        'weight'                       => 'integer|nullable',
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
        'price'                        => 'sometimes|nullable',
        'old_price'                    => 'sometimes|nullable',
    ];
    public $table = 'offline_mall_product_variants';
    public $attachMany = [
        'temp_images' => File::class,
        'downloads'   => File::class,
    ];
    public $belongsTo = [
        'product'      => Product::class,
        'cart_product' => CartProduct::class,
        'image_sets'   => [ImageSet::class, 'key' => 'image_set_id'],
    ];
    public $morphMany = [
        'property_values'       => [PropertyValue::class, 'name' => 'describable'],
        'customer_group_prices' => [CustomerGroupPrice::class, 'name' => 'priceable'],
    ];

    protected $fillable = [
        'product_id',
        'image_set_id',
        'stock',
        'name',
        'published',
        'price',
        'old_price',
        'weight',
        'allow_out_of_stock_purchases',
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
                    'describable_id'   => $variant->id,
                    'describable_type' => Variant::MORPH_KEY,
                    'property_id'      => $id,
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

    public function reduceStock(int $quantity): self
    {
        $this->stock -= $quantity;
        if ($this->stock < 0 && $this->allow_out_of_stock_purchases !== true) {
            throw new OutOfStockException($this);
        }

        return tap($this)->save();
    }

    public function custom_fields()
    {
        return optional($this->parent)->custom_fields();
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function getAttribute($attribute)
    {
        $originalValue = parent::getAttribute($attribute);
        $isPriceColumn = $this->isPriceColumn($attribute);

        if (session()->get('mall.variants.disable-inheritance')) {
            return $this->isPriceColumn($attribute) && $originalValue
                ? $this->roundPrice($originalValue)
                : $originalValue;
        }

        // Cache the "parent" product's data.
        if ( ! $this->parent) {
            $this->parent = Product::find($this->attributes['product_id']);
        }

        $parentValues = $this->parent->getAttribute($attribute);

        if ($isPriceColumn) {
            $value = $this->priceGetAttribute($attribute);

            if (is_array($value)) {
                return array_merge($parentValues ?: [], $value);
            }
        }

        // In case of an empty Array or Collection we want to
        // return the parent's values
        $notEmpty = true;
        if ($originalValue instanceof Collection) {
            $notEmpty = $originalValue->count() > 0;
        } elseif (is_array($originalValue)) {
            $notEmpty = count($originalValue) > 0;
        }

        return $originalValue !== null && $notEmpty ? $originalValue : $parentValues;
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

    public function getPriceColumns()
    {
        return ['price', 'old_price'];
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

    protected function notNullthy($value): bool
    {
        if ($value instanceof \Illuminate\Support\Collection) {
            return $value->count() > 0;
        }

        return $value !== null;
    }
}
