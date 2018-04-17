<?php namespace OFFLINE\Mall\Models;

use Illuminate\Support\Collection;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Classes\Traits\CustomFields;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\Images;
use OFFLINE\Mall\Classes\Traits\Price;
use System\Models\File;

class Variant extends \Model
{
    use Validation;
    use SoftDelete;
    use Images;
    use HashIds;
    use CustomFields;
    use Price {
        getAttribute as priceGetAttribute;
    }

    public $slugs = [];
    public $dates = ['deleted_at'];
    public $with = ['product'];
    public $jsonable = ['price', 'old_price'];
    public $casts = [
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
        'id'                           => 'integer',
        'stock'                        => 'integer',
    ];
    public $rules = [
        'name'                         => 'required',
        'product_id'                   => 'required|exists:offline_mall_products,id',
        'stock'                        => 'integer',
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
        'price'                        => 'sometimes|nullable',
        'old_price'                    => 'sometimes|nullable',
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
        parent::boot();
        static::saved(function (Variant $variant) {
            $values = post('VariantPropertyValues');
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

        return $originalValue && $notEmpty ? $originalValue : $parentValues;
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
